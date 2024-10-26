<?php

namespace Ppm\Core\Commands\Build;

use Exception;
use Ppm\Core\Solution;
use Ppm\Framework\System\Proc\CommandConfiguration;
use Ppm\Framework\System\Proc\CommandLauncher;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class ExecuteScriptCommand extends CommandBase
{
    public function getDescription(): string
    {
        return "Build project and run script";
    }

    public function execute(array $parameters, array $options): void
    {
        $scriptName = $parameters['script'];
        $solution = Solution::getSolutionOrThrow();
        $script = $solution->getScript($scriptName);
        if (is_null($script))
            throw new Exception("Script {$script} not found");
        $directory = $solution->buildProject($script['project']);
        chdir($directory);
        $command = new CommandConfiguration(...$script['command']);
        CommandLauncher::launch($command);
    }
}