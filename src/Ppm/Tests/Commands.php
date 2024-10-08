<?php

namespace Ppm\Tests;

use Ppm\Framework\Assembly;
use Ppm\Framework\CurrentAssembly;

class Commands
{
    public static function runTests(array $parameters, array $options): void
    {
        $assembly = CurrentAssembly::getAssembly();
        $project = $parameters['project'];
        $assembly->includeAndLoad(getcwd() . DIRECTORY_SEPARATOR . $project . '.phar');
        $tester = new TestRunner();
        $tester->run();
    }
}