<?php

namespace PPM\Commands;

use Packages\PackagesController;
use Terminal\CommandRouting\CommandBase;

class DownloadPackage extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $name = $parameters['name'];
        $version = $parameters['version'];

        $packageController = new PackagesController();
        $remoteManager = $packageController->getRemoteManager();
        $package = $remoteManager->find($name, $version);
        if (is_null($package))
            throw new \Exception("Package {$name}:{$version} not found");

        $remoteManager->download($package);
        echo "Package {$name}:{$version} loaded";
    }
}