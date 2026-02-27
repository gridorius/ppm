<?php

namespace Ppm\Core\Commands\Build;

use Ppm\Builder\BuildManager;
use Ppm\Core\Solution;
use Ppm\Framework\Filesystem\PathUtils;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class BuildCommand extends CommandBase
{
    protected array $options = [
        'values' => [
            'o',
        ],
        'counters' => [
            'w',
            'd'
        ]
    ];

    public function execute(array $parameters, array $options, array $argv): void
    {
        $project = $parameters['project'];
        $solution = Solution::getSolutionOrThrow();
        if (key_exists('d', $options))
            [$directory, $contexts] = $solution->buildDebugProject($project, !empty($options['o']) ? PathUtils::resolveRelativePath(getcwd(), $options['o']) : null);
        else
            [$directory, $contexts] = $solution->buildProject($project, !empty($options['o']) ? PathUtils::resolveRelativePath(getcwd(), $options['o']) : null);

        if (key_exists('w', $options))
            while (true) {
                foreach ($contexts as &$context) {
                    $projectFiles = $context->getProjectFiles();
                    $manifest = $context->getManifest();
                    $projectFiles->scan();
                    $changedFiles = $manifest->compareHashes($projectFiles->getHashes());
                    $removedFiles = $manifest->getRemovedFiles($projectFiles->getHashes());
                    if (!empty($changedFiles) || !empty($removedFiles)) {
                        echo "Changed: " . implode(", ", array_values($changedFiles)) . "\n";
                        echo "Removed: " . implode(", ", array_values($removedFiles)) . "\n";
                        $newContext = $projectFiles->getBuildDebugContext();
                        BuildManager::buildProject($newContext, $directory);
                        $context = $newContext;
                    }
                }
                sleep(1);
            }
    }

    public function getDescription(): string
    {
        return "build project\n    o - out directory";
    }
}