<?php

namespace PPM\Commands;

use Builder\Configuration\ConfigurationCollector;
use Packages\PackagesController;
use Exception;
use Terminal\CommandRouting\CommandBase;
use Utils\PathUtils;

class Restore extends CommandBase
{

    public function execute(array $parameters, array $options): void
    {
        $restoreDir = $parameters['restore_directory'] ?? getcwd();
        $packageController = new PackagesController();
        $pathToProjectFile = PathUtils::findProj($restoreDir);
        $configurationCollection = (new ConfigurationCollector())->collectFromProjectFile($pathToProjectFile);
        $packageController->getRemoteManager()->restore($configurationCollection);
    }
}