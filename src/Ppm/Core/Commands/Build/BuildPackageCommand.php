<?php

namespace Ppm\Core\Commands\Build;

use Ppm\Core\Solution;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class BuildPackageCommand extends CommandBase
{
    public function getDescription(): string
    {
        return "Create new local package from project";
    }

    public function execute(array $parameters, array $options, array $argv): void
    {
        $project = $parameters['project'];
        $solution = Solution::getSolutionOrThrow();
        $solution->buildPackage($project);
    }
}