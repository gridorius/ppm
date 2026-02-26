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
use Ppm\Framework\Utils\OSUtils;

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

        if (empty($script['pipe']))
            throw new Exception("Script {$scriptName} has no pipe defined");
        [$directory, $contexts] = $solution->buildDebugProject($script['project']);
        chdir($directory);

        $pipe = $script['pipe'];
        foreach ($pipe as $name => &$commandDescription) {
            $command = null;
            if (!empty($commandDescription['entrypoint'])) {
                $arguments = $commandDescription['arguments'] ?? [];
                $command = Application::createCommandByPath($directory . DIRECTORY_SEPARATOR . $script['project'] . '.phar', $commandDescription['entrypoint'], ...$arguments);
            } else if (!empty($commandDescription['command'])) {
                $command = new CommandConfiguration(...$commandDescription['command'], ...$argv);
            } else
                throw new Exception('Invalid command description: ' . $name);

            if (!empty($commandDescription['cwd']))
                $command->setCwd($directory . '/' . $commandDescription['cwd']);

            $commandDescription['__command'] = $command;
        }

        foreach ($pipe as &$description)
            $description['__process'] = CommandLauncher::launch($description['__command']);

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
                        echo "Changed: " . implode(", ", array_values($changedFiles)) . "\n";
                        echo "Removed: " . implode(", ", array_values($removedFiles)) . "\n";
                        $projectName = $context->getConfiguration()->getProjectInfo()->getName();
                        if (!in_array($projectName, $ignore))
                            $needRestart = true;

                        $newContext = $projectFiles->getBuildDebugContext();
                        BuildManager::buildProject($newContext, $directory);
                        $context = $newContext;

//                        $relations = $context->getManifest()->getFileRelations();
//                        $context->getManifest()->clearChanged($removedFiles);
//                        $newContext = $projectFiles->removeUnchanged($changedFiles)->getBuildDebugContext();
//                        $newContext->getManifest()->mergeParent($context->getManifest());
//                        BuildManager::updateProject($newContext, $relations, $removedFiles, $directory);
//                        $context = $newContext;
                    }
                }
                if ($needRestart) {
                    foreach ($pipe as &$description) {
                        if ($description['watch']) {
                            OSUtils::kill($description['__process']->getPid());
                            $description['__process'] = CommandLauncher::launch($description['__command']);
                        }
                    }
                }
                usleep(100000);
            }
    }
}