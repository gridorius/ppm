<?php

namespace Ppm\Core\Commands\Sources;

use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class SourceListCommand extends CommandBase
{
    public function getDescription(): string
    {
        return "Show source list";
    }

    public function execute(array $parameters, array $options): void
    {
        $packageController = new PackagesManager();
        $sources = $packageController->getSources();

        if (count($sources) == 0) {
            echo "Sources is empty\n";
            return;
        }

        echo "Package sources:\n";
        foreach ($sources as $alias => $source) {
            $authorized = $source->hasToken() ? 'true' : 'false';
            echo "\t{$alias} - {$source->getPath()}, Has token: {$authorized}\n";
        }
    }
}