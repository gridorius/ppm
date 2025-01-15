<?php

namespace Ppm\Framework\System\Proc;

use Exception;

class CommandLauncher
{
    /**
     * @var LaunchedProcess[]
     */
    private static array $processes = [];
    private static bool $shutdownRegistered = false;

    public static function launch(CommandConfiguration $configuration): LaunchedProcess
    {
        $pipes = [];
        $descriptors = [];
        $configuration->configureDescriptors($descriptors);
        $processResource = proc_open($configuration->getCommand(), $descriptors, $pipes);

        if (!is_resource($processResource))
            throw new Exception("Process opening failed");

        $process = new LaunchedProcess($processResource, $pipes, $configuration);
        static::$processes[] = $process;
        if (!static::$shutdownRegistered)
            static::registerShutdown();

        return $process;
    }

    public static function ps(): array
    {
        return static::$processes;
    }

    private static function registerShutdown(): void
    {
        register_shutdown_function(function () {
            foreach (static::ps() as $process)
                $process->close();
        });
    }
}