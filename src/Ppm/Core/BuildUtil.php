<?php

namespace Ppm\Core;

use Ppm\Builder\BuildManager;
use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\ConfigurationCollection;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Packages\PackagesManager;

class BuildUtil
{
    public static function getProjectConfiguration(string $pathToProjectFile): ConfigurationCollection
    {
        $mainProject = new Configuration($pathToProjectFile);
        return $mainProject->buildConfigurationCollection();
    }

    public static function buildProject(string $pathToProjectFile, string $outDir): void
    {
        $configurationCollection = static::getProjectConfiguration($pathToProjectFile);
        static::buildFromConfigurationCollection($configurationCollection, $outDir);
    }

    public static function buildFromConfigurationCollection(ConfigurationCollection $configurationCollection, string $outDir): void
    {
        $packageManager = new PackagesManager();
        $storage = $packageManager->getStorage();
        $packageManager->getRestoreService()->restore($configurationCollection->getPackages());
        BuildManager::buildFromConfigurationCollection($configurationCollection, $outDir);
        BuildManager::AddFrameworkPhar($outDir);
        $packages = $storage->getDependencyTreeBuilder()->buildPackagesTree($configurationCollection->getPackages());
        $storage->extractPackages($packages->getFound(), new Directory($outDir));
    }

    public static function buildFromConfigurationCollectionWithoutDependencies(ConfigurationCollection $configurationCollection, string $outDir): void
    {
        $packageManager = new PackagesManager();
        $packageManager->getRestoreService()->restore($configurationCollection->getPackages());
        BuildManager::buildFromConfigurationCollection($configurationCollection, $outDir);
        BuildManager::AddFrameworkPhar($outDir);
    }
}