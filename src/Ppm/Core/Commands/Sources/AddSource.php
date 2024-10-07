<?php

namespace Ppm\Core\Commands\Sources;

use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class AddSource extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $packageController = new PackagesManager();
        $packageController->addSource($parameters['source'], $parameters['alias']);
        echo "Source {$parameters['source']} added\n";
    }

    public function getDescription(): string
    {
        return 'add source to local registry';
    }
}