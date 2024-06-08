<?php

namespace PPM\Commands;

use Packages\PackagesController;
use PPM\Commands\Contracts\CommandBase;

class SourceList extends CommandBase
{
    public function execute(array $argv)
    {
        $packageController = new PackagesController();
        $sources = $packageController->getSources();

        if (count($sources) == 0) {
            echo "Sources is empty\n";
            return;
        }

        echo "Package sources:\n";
        foreach ($sources as $path => $source) {
            $authorized = $source->hasToken() ? 'true' : 'false';
            echo "\t{$path}, Has token: {$authorized}\n";
        }
    }
}