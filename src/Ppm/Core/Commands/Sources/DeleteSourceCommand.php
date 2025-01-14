<?php

namespace Ppm\Core\Commands\Sources;

use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class DeleteSourceCommand extends CommandBase
{
    public function getDescription(): string
    {
        return "Delete source";
    }

    public function execute(array $parameters, array $options, array $argv): void
    {
        $packageController = new PackagesManager();
        $packageController->deleteSource($parameters['source']);
        echo "Source {$parameters['source']} removed\n";
    }
}