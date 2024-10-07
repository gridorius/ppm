<?php

namespace Ppm\Core\Commands\Sources;

use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class DeleteSource extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $packageController = new PackagesManager();
        $packageController->deleteSource($parameters['source']);
        echo "Source {$parameters['source']} removed\n";
    }
}