<?php

namespace Ppm\Core\Commands\Builders;

use Ppm\Core\Solution;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class BuildPackage extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $project = $parameters['project'];
        $solution = Solution::getSolutionOrThrow();
        $solution->checkProject($project);
        $manager = new PackagesManager();
        $manager->getBuilder()->build($solution->getProjectPath($project));
    }
}