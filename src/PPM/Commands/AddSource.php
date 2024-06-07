<?php

namespace PPM\Commands;

use Packages\PackagesController;
use PPM\Commands\Contracts\CommandBase;
use Exception;

class AddSource extends CommandBase
{

    public function execute(array $argv)
    {
        if (empty($argv[0]))
            throw new Exception("Expected parameter source");
        $packageController = new PackagesController();
        $packageController->addSource($argv[0]);
        echo "Source {$argv[0]} added\n";
    }
}