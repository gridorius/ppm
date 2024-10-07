<?php

namespace Ppm\Core\Commands\Packages;

use Ppm\Builder\Configuration\Configuration;
use Ppm\Core\Solution;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class RestoreCommand extends CommandBase
{
    public function getDescription(): string
    {
        return "Restore project dependency";
    }

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