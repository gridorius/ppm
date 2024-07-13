<?php

namespace PPM\Commands;

use Packages\PackagesController;
use Exception;
use Terminal\CommandRouting\CommandBase;

class DeleteSource extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $packageController = new PackagesController();
        $packageController->deleteSource($parameters['source']);
        echo "Source {$parameters['source']} removed\n";
    }
}