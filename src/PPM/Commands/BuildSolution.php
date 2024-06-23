<?php

namespace PPM\Commands;

use Builder\BuildManager;
use Builder\Configuration\ConfigurationCollection;
use Builder\Configuration\ConfigurationCollector;
use Packages\PackagesController;
use Terminal\CommandRouting\CommandBase;
use Utils\PathUtils;

class BuildSolution extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $outDir = $options['o'] ?? getcwd() . '/out';
        $currentDir = getcwd();
        if (is_null($parameters['build_directory'])) {
            $buildDir = $currentDir;
        } else {
            $buildDir = PathUtils::resolveRelativePath($currentDir, $parameters['build_directory']);
        }

        $buildDir = preg_replace("/[\\\\\/]$/", '', $buildDir);

        $outDir = PathUtils::resolveRelativePath($currentDir, $outDir);
        $pathToSlnFile = PathUtils::findSln($buildDir);
        $sln = PathUtils::getJson($pathToSlnFile);

        $packageController = new PackagesController();
        $buildManager = new BuildManager();
        $collector = new ConfigurationCollector();
        $configurationCollection = new ConfigurationCollection();
        foreach ($sln['projects'] as $path) {
            $collector->collectReference($buildDir . DIRECTORY_SEPARATOR . $path, $configurationCollection);
        }
        $packageController->getRemoteManager()->restore($configurationCollection);
        $configurationCollection->setVersionIfEmpty($sln['version']);
        $buildManager->buildFromConfigurationCollection($configurationCollection, $outDir);
        $buildManager->AddAssemblyPhar($outDir);
        $packageController->unpackPackagesRecursive($configurationCollection, $outDir);
    }
}