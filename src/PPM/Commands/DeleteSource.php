<?php

namespace PPM\Commands;

use PPM\Commands\Contracts\CommandBase;
use Exception;
use Packages\PackageManager;

class DeleteSource extends CommandBase
{

    public function execute(array $argv)
    {
        if (empty($argv[0]))
            throw new Exception("Expected parameter source");
        $manager = new PackageManager();
        $remote = $manager->getRemote();
        $remote->removeSource($argv[0]);
        echo "Source {$argv[0]} removed\n";
    }
}