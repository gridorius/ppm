<?php

namespace Ppm\Core\Commands\Builders;

use Ppm\Core\Services\BuildService;
use Ppm\Core\Solution;
use Ppm\Framework\Filesystem\PathUtils;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class Build extends CommandBase
{
    protected array $options = [
        'values' => [
            'o'
        ]
    ];

    public function execute(array $parameters, array $options): void
    {
        $outDir = PathUtils::resolveRelativePath(getcwd(), $options['o'] ?? getcwd() . '/out');
        $project = $parameters['project'];
        $solution = Solution::getSolutionOrThrow();
        $solution->checkProject($project);
        $buildService = new BuildService();
        $buildService->buildProject($solution->getProjectPath($project), $outDir);
    }

    public function getDescription(): string
    {
        return "build project\n    o - out directory";
    }
}