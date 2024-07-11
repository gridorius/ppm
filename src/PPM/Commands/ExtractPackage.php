<?php

namespace PPM\Commands;

use Packages\PackagesController;
use Terminal\CommandRouting\CommandBase;

class ExtractPackage extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $name = $parameters['name'];
        $version = $parameters['version'];
        $outDirectory = $parameters['out_directory'] ?? getcwd();

        $packageController = new PackagesController();
        $localPackage = $packageController->getLocalManager()->get($name, $version);
        if (is_null($localPackage))
            throw new \Exception("Package {$name}:{$version} not found in local registry");

        if (!is_dir($outDirectory))
            mkdir($outDirectory, 0755, true);

        $localPackage->unpack($outDirectory);
    }
}