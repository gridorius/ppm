<?php

namespace PPM\Commands;

use Packages\PackagesController;
use Exception;
use Terminal\CommandRouting\CommandBase;

class AddSource extends CommandBase
{

    public function execute(array $parameters, array $options): void
    {
        $packageController = new PackagesController();
        $packageController->addSource($parameters['source'], $parameters['alias']);
        echo "Source {$parameters['source']} added\n";
    }
}