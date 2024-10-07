<?php

namespace Ppm\Core;

use Exception;
use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\ProjectFile;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\PathUtils;
use Ppm\Packages\PackagesManager;

class Solution
{
    private string $path;
    private string $directory;
    private array $data;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->directory = dirname($this->path);
        $this->data = PathUtils::parseJson($this->path);
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
        $globalPackages = $manager->getStorage();
        $projects = $this->getData()['projects'];

        $packages = [];
        foreach ($projects as $project) {
            $configuration = new Configuration($this->getProjectPath($project));
            foreach ($configuration->buildConfigurationCollection()->getPackages() as $package)
                $packages[] = $package;
        }

        $this->getPackagesDirectory()->clear();
        $packages = array_unique($packages);
        $globalPackages->unpackPackages($packages, $this->getPackagesDirectory());
    }

    public function getData(): array
    {
        return $this->data;
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
        return Directory::from($this->directory . DIRECTORY_SEPARATOR . '.ppm_packages')->create();
    }

    public function checkProject(string $projectName): void
    {
        if (!$this->hasProject($projectName))
            throw new Exception("Project {$projectName} not found");
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
}