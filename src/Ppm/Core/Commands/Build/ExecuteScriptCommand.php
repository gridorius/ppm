<?php

namespace Ppm\Core\Commands\Build;

use Exception;
use Ppm\Builder\BuildContext;
use Ppm\Builder\BuildManager;
use Ppm\Builder\ContextBuilder;
use Ppm\Core\Solution;
use Ppm\Framework\System\Proc\CommandConfiguration;
use Ppm\Framework\System\Proc\CommandLauncher;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class ExecuteScriptCommand extends CommandBase
{
    protected array $options = [
        'values' => [
            'i'
        ]
    ];

    public function getDescription(): string
    {
        return "Build project and run script";
    }

    public function execute(array $parameters, array $options, array $argv): void
    {
        $scriptName = $parameters['script'];
        $solution = Solution::getSolutionOrThrow();
        $script = $solution->getScript($scriptName);
        if (is_null($script))
            throw new Exception("Script {$script} not found");
        [$directory, $contexts] = $solution->buildProject($script['project']);
        chdir($directory);
        $command = new CommandConfiguration(...$script['command'], ...$argv);
        $process = CommandLauncher::launch($command);
        $ignore = $options['i'] ?? [];
        /**
         * @var BuildContext[] $contexts
         */
        while (true) {
            $needRestart = false;
            foreach ($contexts as &$context) {
                $projectFiles = $context->getProjectFiles();
                $projectFiles->scan();
                if ($projectFiles->isChanged()) {
                    $projectName = $context->getConfiguration()->getProjectInfo()->getName();
                    if (!in_array($projectName, $ignore))
                        $needRestart = true;
                    foreach ($context->getOuterFiles() as $relativePath => $absolutePath) {
                        $path = $directory . DIRECTORY_SEPARATOR . $relativePath;
                        unlink($path);
                    }
                    $context = ContextBuilder::build($projectFiles, $context->getConfiguration());
                    BuildManager::buildProject($context, $directory);
                }
            }
            if ($needRestart) {
                posix_kill($process->getPid(), SIGKILL);
                $process = CommandLauncher::launch($command);
            }
            usleep(100000);
        }
    }
}