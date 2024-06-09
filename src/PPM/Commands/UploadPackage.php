<?php

namespace PPM\Commands;

use Builder\Configuration\ConfigurationCollector;
use Packages\PackagesController;
use Packages\Source;
use Exception;
use Terminal\CommandRouting\CommandBase;
use Utils\PathUtils;

class UploadPackage extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $sourcePath = $parameters['source'];
        $projectDir = $parameters['project_directory'] ?? getcwd();
        $pathToProjectFile = PathUtils::findProj($projectDir);

        $configurationCollection = (new ConfigurationCollector())->collect($pathToProjectFile);
        $mainConfiguration = $configurationCollection->getMainConfiguration();
        $name = $mainConfiguration->getName();
        $version = $mainConfiguration->getVersion();
        $packageController = new PackagesController();
        $remoteManager = $packageController->getRemoteManager();
        $localManager = $packageController->getLocalManager();
        $sources = $packageController->getSources();
        if (is_null($localPackage = $localManager->get($name, $version)))
            throw new Exception("Package {$name}:{$version} not found in local registry");

        $source = $sources->has($sourcePath) ? $sources->get($sourcePath) : new Source($sourcePath);
        $remoteManager->upload($localPackage, $source);
    }
}