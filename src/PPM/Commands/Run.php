<?php

namespace PPM\Commands;

use Services\BuildService;
use Terminal\CommandRouting\CommandBase;
use Utils\PathUtils;

class Run extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $buildDir = is_null($parameters['build_directory'])
            ? getcwd()
            : PathUtils::resolveRelativePath(getcwd(), $parameters['build_directory']);
        $tmp = TMP_DIRECTORY . DIRECTORY_SEPARATOR . pathinfo($parameters['build_directory'], PATHINFO_BASENAME);
        PathUtils::createDirectory($tmp);

        $buildService = new BuildService();
        $configurations = $buildService->buildProject($buildDir, $tmp);
        $entrypoint = $configurations->getMainConfiguration()->getRunner() . '.php';
        $entrypointPath = realpath($tmp . DIRECTORY_SEPARATOR . $entrypoint);
        echo "run {$entrypointPath}" . PHP_EOL;
        $proc = proc_open(['php', '-f', $entrypointPath], [], $pipes);
        register_shutdown_function(function ($proc) {
            proc_close($proc);
        }, $proc);
    }
}