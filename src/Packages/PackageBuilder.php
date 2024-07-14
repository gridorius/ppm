<?php

namespace Packages;

use Builder\BuildManager;
use Builder\Configuration\ConfigurationCollector;
use Builder\Configuration\Contracts\IConfigurationCollector;
use Builder\Contracts\IBuildManager;
use Packages\Contracts\ILocalManager;
use Packages\Contracts\IPackageBuilder;
use Utils\FileUtils;

class PackageBuilder implements IPackageBuilder
{
    private IBuildManager $buildManager;
    private IConfigurationCollector $configurationCollector;
    private ILocalManager $localManager;

    public function __construct(ILocalManager $localManager)
    {
        $this->buildManager = new BuildManager();
        $this->configurationCollector = new ConfigurationCollector();
        $this->localManager = $localManager;
    }

    public function build(string $pathToProjectFile): void
    {
        $configurationCollection = $this->configurationCollector->collectFromProjectFile($pathToProjectFile);
        $configuration = $configurationCollection->getMainConfiguration();
        $packageDirectory = $this->localManager->getLocalPath($configuration->getName(), $configuration->getVersion());
        $configurationCollection->setVersionIfEmpty($configurationCollection->getMainConfiguration()->getVersion());

        if (is_dir($packageDirectory))
            FileUtils::removeDirectory($packageDirectory);
        $this->buildManager->buildFromConfigurationCollection($configurationCollection, $packageDirectory);

        $packageMetadata = Metadata::createFromConfigurationCollection($configurationCollection);
        $prefixLength = strlen($packageDirectory) + 1;
        foreach (glob($packageDirectory . DIRECTORY_SEPARATOR . '*') as $path) {
            $relativePath = substr($path, $prefixLength);
            $packageMetadata['hashes'][$relativePath] = hash_file('sha256', $path);
        }
        Metadata::createMetadataFile($packageDirectory, $packageMetadata);

        echo "Package {$configuration->getName()}:{$configuration->getVersion()} built\n";
    }
}