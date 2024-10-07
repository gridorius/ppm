<?php

namespace Ppm\Core\Commands\Builders;

use Ppm\Builder\Configuration\Configuration;
use Ppm\Core\Solution;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class Restore extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $project = $parameters['project'];
        $solution = Solution::getSolutionOrThrow();
        $solution->checkProject($project);
        $packageManager = new PackagesManager();
        $configuration = new Configuration($solution->getProjectPath($project));
        $configurationCollection = $configuration->buildConfigurationCollection();
        $packageManager->getRestoreService()->restore($configurationCollection->getPackages());
    }
}