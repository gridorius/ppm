<?php

namespace PPM\Commands;

use Packages\PackagesController;
use PPM\Commands\Contracts\CommandBase;

class SourceList extends CommandBase
{
    public function execute(array $argv)
    {
        $packageController = new PackagesController();
        echo "Package sources:\n";
        foreach ($packageController->getSources() as $path => $source) {
            $authorized = $source->hasToken() ? 'true' : 'false';
            echo "\t{$path}, Authorized: {$authorized}\n";
        }
    }
}