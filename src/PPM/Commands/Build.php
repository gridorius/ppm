<?php

namespace PPM\Commands;

use Builder\BuildManager;
use Builder\Configuration\ConfigurationCollector;
use Packages\PackagesController;
use PPM\Commands\Contracts\CommandBase;
use Utils\PathUtils;

class Build extends CommandBase
{
    public function execute(array $argv)
    {
        $outDir = $argv[0] ?? getcwd() . '/out';
        $buildDir = $argv[1] ?? getcwd();

        $currentDir = getcwd();
        if (empty($buildDir)) {
            $buildDir = $currentDir;
        } else {
            $buildDir = PathUtils::resolveRelativePath($currentDir, $buildDir);
        }

        $outDir = PathUtils::resolveRelativePath($currentDir, $outDir);
        $pathToProjectFile = PathUtils::findProj($buildDir);
        $packageController = new PackagesController();
        $buildManager = new BuildManager();

        $configurationCollection = (new ConfigurationCollector())->collect($pathToProjectFile);
        $packageController->getRemoteManager()->restore($configurationCollection);
        $buildManager->buildFromConfigurationCollection($configurationCollection, $outDir);
        $buildManager->AddAssemblyPhar($outDir);
        $packageController->getLocalManager()->unpackPackagesRecursive($configurationCollection, $outDir);
    }
}