<?php

namespace Ppm\Core\Services;

use Ppm\Builder\BuildManager;
use Ppm\Builder\Configuration\Configuration;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Packages\PackagesManager;

class BuildService
{
    public function buildProject(string $pathToProjectFile, string $outDir): Configuration
    {
        $packageManager = new PackagesManager();
        $storage = $packageManager->getStorage();

        $mainProject = new Configuration($pathToProjectFile);
        $configurationCollection = $mainProject->buildConfigurationCollection();
        $packageManager->getRestoreService()->restore($configurationCollection->getPackages());
        BuildManager::buildFromConfigurationCollection($configurationCollection, $outDir);
        BuildManager::AddAssemblyPhar($outDir);
        $packages = $storage->getDependencyTreeBuilder()->buildPackagesTree($configurationCollection->getPackages());
        $storage->extractPackages($packages->getFound(), new Directory($outDir));
        return $mainProject;
    }
}