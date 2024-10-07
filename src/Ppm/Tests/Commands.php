<?php

namespace Ppm\Tests;

use Ppm\Framework\Assembly;

class Commands
{
    public static function runTests(array $parameters, array $options): void
    {
        $project = $parameters['project'];
        Assembly::includePhar(getcwd() . DIRECTORY_SEPARATOR . $project . '.phar');
        Assembly::preload();
        $tester = new TestRunner();
        $tester->run();
    }
}