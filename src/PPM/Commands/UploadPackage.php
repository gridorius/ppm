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
        $name = $parameters['name'];
        $version = $parameters['version'];

        $packageController = new PackagesController();
        $remoteManager = $packageController->getRemoteManager();
        $localManager = $packageController->getLocalManager();
        $sources = $packageController->getSources();
        if (is_null($localPackage = $localManager->get($name, $version)))
            throw new Exception("Package {$name}:{$version} not found in local registry");

        $source = $sources->has($sourcePath) ? $sources->get($sourcePath) : $sources->createSource($sourcePath);
        $remoteManager->upload($localPackage, $source);
    }
}