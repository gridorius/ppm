<?php

namespace PPM\Commands;

use Builder\Configuration\ConfigurationCollector;
use Packages\PackagesController;
use Packages\Source;
use PPM\Commands\Contracts\CommandBase;
use Exception;
use Utils\PathUtils;

class UploadPackage extends CommandBase
{
    public function execute(array $argv)
    {
        if (empty($argv[0]))
            throw new Exception("Expected parameter source");
        $sourcePath = $argv[0];
        $projectDir = $argv[1] ?? getcwd();
        $pathToProjectFile = PathUtils::findProj($projectDir);

        $configurationCollection = (new ConfigurationCollector())->collect($pathToProjectFile);
        $mainConfiguration = $configurationCollection->getMainConfiguration();
        $name = $mainConfiguration->getName();
        $version = $mainConfiguration->getVersion();
        $packageController = new PackagesController();
        $remoteManager = $packageController->getRemoteManager();
        $localManager = $packageController->getLocalManager();
        $sources = $packageController->getSources();
        if (!$localManager->exist($name, $version))
            throw new Exception("Package {$name}:{$version} not found in local registry");

        $source = $sources->has($sourcePath) ? $sources->get($sourcePath) : new Source($sourcePath);
        $remoteManager->upload($localManager->get($name, $version), $source);
    }
}