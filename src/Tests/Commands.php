<?php

namespace Tests;

use Assembly\Assembly;
use Services\BuildService;
use Utils\FileUtils;

class Commands
{
    public static function runTests(array $parameters, array $options): void
    {
        $outDirectory = TMP_DIRECTORY.DIRECTORY_SEPARATOR.microtime().'_test';
        $buildService = new BuildService();
        $configurations = $buildService->buildProject(getcwd(), $outDirectory);
        $pathToPhar = $outDirectory.DIRECTORY_SEPARATOR.$configurations->getMainConfiguration()->getName().'.phar';
        Assembly::includePhar($pathToPhar);
        Assembly::preloadTypes();
        Assembly::includeScripts();
        $tester = new TestRunner();
        $tester->run();
        FileUtils::removeDirectory($outDirectory);
    }
}