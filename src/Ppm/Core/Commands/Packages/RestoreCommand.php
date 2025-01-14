<?php

namespace Ppm\Core\Commands\Packages;

use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\ConfigurationCollection;
use Ppm\Core\Solution;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class RestoreCommand extends CommandBase
{
    public function getDescription(): string
    {
        return "Restore project dependency";
    }

    public function execute(array $parameters, array $options, array $argv): void
    {
        $project = $parameters['project'];
        $solution = Solution::getSolutionOrThrow();
        $solution->checkProject($project);
        $packageManager = new PackagesManager();
        $configurationCollection = ConfigurationCollection::from($solution->getProjectPath($project));
        $packageManager->getRestoreService()->restore($configurationCollection->getPackages());
    }
}