<?php

namespace PPM\Commands;

use Builder\Configuration\ConfigurationCollector;
use Packages\PackagesController;
use PPM\Commands\Contracts\CommandBase;
use Exception;
use Utils\PathUtils;

class Restore extends CommandBase
{

    public function execute(array $argv)
    {
        $restoreDir = $argv[0] ?? getcwd();

        if (empty($restoreDir))
            throw new Exception("Expected parameter build directory");

        $packageController = new PackagesController();
        $pathToProjectFile = PathUtils::findProj($restoreDir);
        $configurationCollection = (new ConfigurationCollector())->collect($pathToProjectFile);
        $packageController->getRemoteManager()->restore($configurationCollection);
    }
}