<?php

namespace PPM\Commands;

use Assembly\Exception;
use Packages\Compactor;
use Packages\PackagesController;
use Terminal\CommandRouting\CommandBase;

class CompactPackage extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $localPackage = (new PackagesController())->getLocalManager()->get($parameters['name'], $parameters['version']);
        if (is_null($localPackage))
            throw new Exception("Package not found");
        Compactor::compact($localPackage, getcwd());
    }
}