<?php

namespace PPM\Commands;

use Assembly\Assembly;
use Terminal\CommandRouting\CommandBase;
use Tests\Tester;
use Utils\PathUtils;

class RunTests extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $testProject = $parameters['test_project_phar'];
        $pathToPhar = PathUtils::resolveRelativePath(getcwd(), $testProject);
        Assembly::includePhar($pathToPhar);
        Assembly::preloadTypes();
        Assembly::includeScripts();
        $tester = new Tester();
        $tester->run();
    }
}