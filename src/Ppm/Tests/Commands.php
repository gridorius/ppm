<?php

namespace Ppm\Tests;

use Ppm\Framework\Application;

class Commands
{
    public static function runTests(array $parameters, array $options): void
    {
        $project = $parameters['project'];
        Application::includeAndLoad(getcwd() . DIRECTORY_SEPARATOR . $project . '.phar');
        $tester = new TestRunner();
        $tester->run();
    }
}