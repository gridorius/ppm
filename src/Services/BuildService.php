<?php

namespace Services;

use Builder\BuildManager;
use Builder\Configuration\ConfigurationCollector;
use Builder\Configuration\Contracts\IConfigurationCollection;
use Packages\PackagesController;
use Utils\PathUtils;

class BuildService
{
    public function buildProject(string $buildDir, string $outDir): IConfigurationCollection
    {
        $pathToProjectFile = PathUtils::getProjOrThrow($buildDir);
        $packageController = new PackagesController();
        $buildManager = new BuildManager();

        $configurationCollection = (new ConfigurationCollector())->collectFromProjectFile($pathToProjectFile);
        $packageController->getRemoteManager()->restore($configurationCollection);
        $configurationCollection->setVersionIfEmpty($configurationCollection->getMainConfiguration()->getVersion());
        $buildManager->buildFromConfigurationCollection($configurationCollection, $outDir);
        $buildManager->AddAssemblyPhar($outDir);
        $packageController->unpackPackagesRecursive($configurationCollection, $outDir);
        return $configurationCollection;
    }
}