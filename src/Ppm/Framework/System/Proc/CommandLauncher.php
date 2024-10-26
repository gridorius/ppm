<?php

namespace Ppm\Framework\System\Proc;

use Exception;

class CommandLauncher
{
    public static function launch(CommandConfiguration $configuration): LaunchedProcess
    {
        $pipes = [];
        $processResource = proc_open($configuration->getCommand(), $configuration->getDescriptors(), $pipes);

        if (!is_resource($processResource))
            throw new Exception("Process opening failed");

        return new LaunchedProcess($processResource, $pipes, $configuration);
    }
}