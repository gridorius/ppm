<?php

namespace Ppm\Framework\System\Proc;

use Exception;

class CommandLauncher
{
    public static function launch(CommandConfiguration $configuration): LaunchedProcess
    {
        $pipes = [];
        $descriptors = [];
        $configuration->configureDescriptors($descriptors);
        $processResource = proc_open($configuration->getCommand(), $descriptors, $pipes);

        if (!is_resource($processResource))
            throw new Exception("Process opening failed");

        return new LaunchedProcess($processResource, $pipes, $configuration);
    }
}