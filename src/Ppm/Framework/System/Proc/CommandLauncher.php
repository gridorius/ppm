<?php

namespace Ppm\Framework\System\Proc;

use Exception;

class CommandLauncher
{
    public function launch(CommandConfiguration $configuration): LaunchedProcess
    {
        $pipes = [];
        $processResource = proc_open($configuration->getCommand(), $configuration->getDescriptors(), $pipes);

        if (!is_resource($processResource))
            throw new Exception("Process opening failed");

        return new LaunchedProcess(new ProcResource($processResource), $pipes);
    }
}