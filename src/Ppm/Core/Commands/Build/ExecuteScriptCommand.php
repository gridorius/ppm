<?php

namespace Ppm\Core\Commands\Build;

use Exception;
use Ppm\Builder\BuildContext;
use Ppm\Builder\BuildManager;
use Ppm\Builder\ContextBuilder;
use Ppm\Core\Solution;
use Ppm\Framework\Application;
use Ppm\Framework\System\Proc\CommandConfiguration;
use Ppm\Framework\System\Proc\CommandLauncher;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class ExecuteScriptCommand extends CommandBase
{
    protected array $options = [
        'values' => [
            'i'
        ],
        'counters' => [
            'w'
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
        $command = null;
        if (!empty($script['entrypoint'])) {
            $arguments = $script['arguments'] ?? [];
            $command = Application::createCommandByPath($directory . DIRECTORY_SEPARATOR . $script['project'] . '.phar', $script['entrypoint'], ...$arguments);
        }
        if (!empty($script['command']))
            $command = new CommandConfiguration(...$script['command'], ...$argv);

        if (is_null($command))
            throw new Exception("Invalid script {$scriptName}");

        $process = CommandLauncher::launch($command);
        $ignore = $options['i'] ?? [];
        /**
         * @var BuildContext[] $contexts
         */
        if (key_exists('w', $options))
            while (true) {
                $needRestart = false;
                foreach ($contexts as &$context) {
                    $projectFiles = $context->getProjectFiles();
                    $manifest = $context->getManifest();
                    $projectFiles->scan();
                    $changedFiles = $manifest->compareHashes($projectFiles->getHashes());
                    $removedFiles = $manifest->getRemovedFiles($projectFiles->getHashes());
                    if (!empty($changedFiles) || !empty($removedFiles)) {
                        $projectName = $context->getConfiguration()->getProjectInfo()->getName();
                        if (!in_array($projectName, $ignore))
                            $needRestart = true;
                        $relations = $context->getManifest()->getFileRelations();
                        $context->getManifest()->clearChanged(array_merge($changedFiles, $removedFiles));
                        $newContext = ContextBuilder::apply($context->getManifest(), $projectFiles->fromChanged($changedFiles), $context->getConfiguration());
                        BuildManager::updateProject($newContext, $relations, $removedFiles, $directory);
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