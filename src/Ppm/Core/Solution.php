<?php

namespace Ppm\Core;

use Exception;
use Ppm\Builder\BuildManager;
use Ppm\Builder\Configuration\ConfigurationCollection;
use Ppm\Builder\Constants;
use Ppm\Builder\ProjectFile;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Filesystem\PathUtils;
use Ppm\Framework\Storage\FileStorage;
use Ppm\Packages\PackagesManager;

class Solution
{
    private string $path;
    private string $directory;
    private array $data;
    private PackagesManager $packagesManager;
    private FileStorage $cache;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->directory = dirname($this->path);
        $this->data = PathUtils::parseJson($this->path);
        $this->packagesManager = new PackagesManager();
        Directory::createDirectory($this->getSolutionDataPath());
        $this->cache = new FileStorage($this->getSolutionDataPath('cache.json'));
    }

    public static function getSolution(): ?Solution
    {
        $solutionPath = static::findSolutionFile();
        return is_null($solutionPath) ? null : new static($solutionPath);
    }

    public static function getSolutionOrThrow(): Solution
    {
        $solutionPath = static::findSolutionFile();
        return is_null($solutionPath) ? throw new Exception("Solution not found") : new static($solutionPath);
    }

    public function unpackPackages(): void
    {
        $manager = new PackagesManager();
        $storage = $manager->getStorage();
        $projects = $this->getData()['projects'];

        $packages = [];
        foreach ($projects as $name => $relativePath)
            foreach (ConfigurationCollection::from($this->getProjectPath($name))->getPackages() as $packageName => $version)
                $packages[$packageName] = $version;

        $this->getPackagesDirectory()->clear();
        $packages = array_unique($packages);
        $packagesDirectory = $this->getPackagesDirectory();
        $packagesTree = $storage->getDependencyTreeBuilder()->buildPackagesTree($packages);
        copy(Path::assemblyCombine(
            Constants::FRAMEWORK_PHAR_NAME),
            $packagesDirectory->getPath() . DIRECTORY_SEPARATOR . Constants::FRAMEWORK_PHAR_NAME
        );
        foreach ($packagesTree->getFound() as $name => $version)
            $storage->get($name, $version)->extractTo($packagesDirectory);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getScript(string $project): ?array
    {
        return $this->data['scripts'][$project] ?? null;
    }

    public function save(array $data): void
    {
        $this->data = $data;
        file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function hasProject(string $projectName): bool
    {
        return key_exists($projectName, $this->data['projects']);
    }

    public function getProjectPath(string $projectName): string
    {
        return ProjectFile::getPathOrThrow($this->directory . DIRECTORY_SEPARATOR . $this->data['projects'][$projectName]);
    }

    public function getPackagesDirectory(): Directory
    {
        return Directory::from($this->getSolutionDataPath('packages'))->create();
    }

    public function getSolutionDataPath(string ...$parts): string
    {
        return Path::combine($this->directory, '.ppm', ...$parts);
    }

    public function checkProject(string $projectName): void
    {
        if (!$this->hasProject($projectName))
            throw new Exception("Project {$projectName} not found");
    }

    public function buildProject(string $name, ?string $outDirectory = null): string
    {
        if (is_null($outDirectory))
            $outDirectory = $this->getDirectory() . DIRECTORY_SEPARATOR . '/bin/' . $name;
        $this->checkProject($name);
        Directory::createDirectory($outDirectory);
        $configurationCollection = ConfigurationCollection::from($this->getProjectPath($name));
        $contexts = $configurationCollection->getContextCollection();
        $hash = $contexts->getHash();
        $projectsCache = $this->cache->getArray('projects');
        if ($projectsCache->get($name) == $hash) {
            echo "Project not changed" . PHP_EOL;
        } else {
            BuildManager::buildFromConfigurationCollection($configurationCollection, $outDirectory);
            $projectsCache->set($name, $hash);
        }
        BuildManager::AddFrameworkPhar($outDirectory);
        $this->extractDependencies($configurationCollection, $outDirectory);

        return $outDirectory;
    }

    public function buildPackage(string $name): void
    {
        $this->checkProject($name);
        $manager = new PackagesManager();
        $manager->getBuilder()->build($this->getProjectPath($name));
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    private static function findSolutionFile(int $iterations = 20): ?string
    {
        $currentPath = getcwd();
        while ($iterations-- > 0 && $currentPath && !file_exists($currentPath . DIRECTORY_SEPARATOR . 'solution.json')) {
            $currentPath = realpath($currentPath . '/..');
        }
        $solutionPath = $currentPath . DIRECTORY_SEPARATOR . 'solution.json';
        if (!file_exists($solutionPath))
            return null;

        return $solutionPath;
    }

    private function restore(ConfigurationCollection $configurationCollection): void
    {
        $this->packagesManager->getRestoreService()->restore($configurationCollection->getPackages());
    }

    private function extractDependencies(ConfigurationCollection $configurationCollection, string $outDirectory): void
    {
        $storage = $this->packagesManager->getStorage();
        $packagesCache = $this->cache->getArray('packages');
        $this->restore($configurationCollection);
        $packages = $storage->getDependencyTreeBuilder()->buildPackagesTree($configurationCollection->getPackages());
        foreach ($packages->getFound() as $name => $version) {
            $package = $storage->get($name, $version);
            if ($packagesCache->get($name) != $package->getMetadata()->getHashSum()) {
                $package->extractTo(Directory::from($outDirectory));
                $packagesCache->set($name, $package->getMetadata()->getHashSum());
            } else {
                echo "Package {$name}:{$version} loaded from cache" . PHP_EOL;
            }
        }
    }
}