<?php

namespace PPM\Commands;

use Builder\BuildManager;
use Builder\Configuration\ConfigurationCollector;
use Packages\PackagesController;
use Terminal\CommandRouting\CommandBase;
use Terminal\OptionParser;
use Utils\PathUtils;

class Build extends CommandBase
{
    protected array $options = [
        'o' => true
    ];

    public function execute(array $parameters, array $options): void
    {
        $outDir = $options['o'] ?? getcwd() . '/out';
        $currentDir = getcwd();
        if (is_null($parameters['build_directory'])) {
            $buildDir = $currentDir;
        } else {
            $buildDir = PathUtils::resolveRelativePath($currentDir, $parameters['build_directory']);
        }

        $outDir = PathUtils::resolveRelativePath($currentDir, $outDir);
        $pathToProjectFile = PathUtils::findProj($buildDir);
        $packageController = new PackagesController();
        $buildManager = new BuildManager();

        $configurationCollection = (new ConfigurationCollector())->collectFromProjectFile($pathToProjectFile);
        $packageController->getRemoteManager()->restore($configurationCollection);
        $configurationCollection->setVersionIfEmpty($configurationCollection->getMainConfiguration()->getVersion());
        $buildManager->buildFromConfigurationCollection($configurationCollection, $outDir);
        $buildManager->AddAssemblyPhar($outDir);
        $packageController->unpackPackagesRecursive($configurationCollection, $outDir);
    }

    public function getDescription(): string
    {
        return "build project\n    o - out directory";
    }
}