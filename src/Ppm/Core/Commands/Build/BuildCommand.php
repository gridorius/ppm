<?php

namespace Ppm\Core\Commands\Build;

use Ppm\Core\Services\BuildService;
use Ppm\Core\Solution;
use Ppm\Framework\Filesystem\Directory;
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

        if (!empty($options['o']))
            $outDir = PathUtils::resolveRelativePath(getcwd(), $options['o']);
        else
            $outDir = $solution->getDirectory() . DIRECTORY_SEPARATOR . '/Build/' . $project;

        $solution->checkProject($project);
        Directory::createDirectory($outDir);
        $buildService = new BuildService();
        $buildService->buildProject($solution->getProjectPath($project), $outDir);
    }

    public function getDescription(): string
    {
        return "build project\n    o - out directory";
    }
}