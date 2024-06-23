<?php

namespace PPM\Commands;

use Packages\PackagesController;
use Terminal\CommandRouting\CommandBase;

class PackageList extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $packageController = new PackagesController();
        $localManager = $packageController->getLocalManager();
        echo "Local packages:\n";
        foreach ($localManager->toArray() as $name => $versions) {
            $stringVersions = implode(', ', array_keys($versions));
            echo "\t{$name}: ({$stringVersions})\n";
        }
    }
}