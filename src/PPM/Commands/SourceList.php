<?php

namespace PPM\Commands;

use Packages\PackageManager;
use PPM\Commands\Contracts\CommandBase;

class SourceList extends CommandBase
{
    public function execute(array $argv)
    {
        $manager = new PackageManager();
        $sources = $manager->getRemote()->getSources();
        echo "Package sources:\n";
        foreach ($sources as $source)
            echo "\t{$source}\n";
    }
}