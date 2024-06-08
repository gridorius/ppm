<?php

namespace PPM\Commands;

use Builder\BuildManager;
use Builder\Configuration\ConfigurationCollector;
use Packages\PackagesController;
use PPM\Commands\Contracts\CommandBase;
use Terminal\OptionParser;
use Utils\PathUtils;

class Build extends CommandBase
{
    private array $options = [
        'o' => true
    ];

    public function execute(array $argv)
    {
        $options = OptionParser::parse($argv, $this->options);
        $outDir = $options['o'] ?? getcwd() . '/out';
        $currentDir = getcwd();
        if (empty($argv[0])) {
            $buildDir = $currentDir;
        } else {
            $buildDir = PathUtils::resolveRelativePath($currentDir, $argv[0]);
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