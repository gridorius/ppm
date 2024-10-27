<?php

namespace Ppm\Core\Commands\Build;

use Ppm\Core\Solution;
use Ppm\Framework\Filesystem\PathUtils;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class BuildCommand extends CommandBase
{
    protected array $options = [
        'values' => [
            'o'
        ]
    ];

    public function execute(array $parameters, array $options): void
    {
        $project = $parameters['project'];
        $solution = Solution::getSolutionOrThrow();
        $solution->buildProject($project, !empty($options['o']) ? PathUtils::resolveRelativePath(getcwd(), $options['o']) : null);
    }

    public function getDescription(): string
    {
        return "build project\n    o - out directory";
    }
}