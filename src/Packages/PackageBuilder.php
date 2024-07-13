<?php

namespace Packages;

use ArrayIterator;
use Builder\BuildManager;
use Builder\Configuration\ConfigurationCollector;
use Builder\Configuration\Contracts\IConfigurationCollector;
use Builder\Contracts\IBuildManager;
use Builder\FileScanner;
use Packages\Contracts\IPackageBuilder;
use Phar;
use PpmRegistry\Contracts\ILocalManager;
use Utils\PathUtils;

class PackageBuilder implements IPackageBuilder
{
    private string $tmpDirectory;

    private IBuildManager $buildManager;
    private IConfigurationCollector $configurationCollector;
    private ILocalManager $localManager;

    public function __construct(ILocalManager $localManager, string $tmpDirectory)
    {
        $this->tmpDirectory = $tmpDirectory;
        $this->buildManager = new BuildManager();
        $this->configurationCollector = new ConfigurationCollector();
        $this->localManager = $localManager;
        if (!is_dir($tmpDirectory))
            mkdir($tmpDirectory, 0755, true);
    }

    public function build(string $pathToProjectFile): void
    {
        $outDirectory = $this->tmpDirectory . DIRECTORY_SEPARATOR . '_package_' . time();
        $configurationCollection = $this->configurationCollector->collectFromProjectFile($pathToProjectFile);
        $configurationCollection->setVersionIfEmpty($configurationCollection->getMainConfiguration()->getVersion());
        $this->buildManager->buildFromConfigurationCollection($configurationCollection, $outDirectory);
        $configuration = $configurationCollection->getMainConfiguration();

        $packageMetadata = [
            'name' => $configuration->getName(),
            'version' => $configuration->getVersion(),
            'author' => $configuration->getAuthor(),
            'description' => $configuration->getDescription(),
            'depends' => $configurationCollection->getPackages(),
            'hashes' => []
        ];

        $prefixLength = strlen($outDirectory) + 1;
        $packageFiles = [];
        foreach (glob($outDirectory . DIRECTORY_SEPARATOR . '*') as $path) {
            $localPath = substr($path, $prefixLength);
            $packageFiles[$localPath] = $path;
            $packageMetadata['hashes'][$localPath] = hash_file('sha256', $path);
        }

        $packagePath = $this->localManager->getLocalPath($configuration->getName(), $configuration->getVersion());
        if (file_exists($packagePath))
            unlink($packagePath);

        $phar = new Phar($packagePath);
        $phar->startBuffering();
        $phar->buildFromIterator(new ArrayIterator($packageFiles));
        $phar->setMetadata($packageMetadata);
        $phar->stopBuffering();
        foreach ($packageFiles as $path)
            unlink($path);
        rmdir($outDirectory);
        echo "Package {$configuration->getName()}:{$configuration->getVersion()} built\n";
    }

    public function buildResourcesPackage(string $buildDirectory): void
    {
        $pathToProjectFile = PathUtils::findProj($buildDirectory);
        $projName = pathinfo($pathToProjectFile, PATHINFO_BASENAME);
        $info = PathUtils::getJson($pathToProjectFile);
        $name = $info['name'];
        $version = $info['version'];
        $packageMetadata = [
            'name' => $name,
            'version' => $version,
            'author' => $info['author'] ?? '',
            'description' => $info['description'] ?? '',
            'depends' => [],
            'hashes' => []
        ];
        $packagePath = $this->localManager->getLocalPath($name, $version);
        if(file_exists($packagePath))
            unlink($packagePath);
        $phar = new Phar($packagePath);
        $phar->startBuffering();
        $phar->buildFromDirectory($buildDirectory);
        $phar->delete($projName);
        $phar->setMetadata($packageMetadata);
        $phar->stopBuffering();
        echo "Package {$name}:{$version} built\n";
    }
}