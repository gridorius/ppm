<?php

namespace Ppm\Core\Commands\Packages;

use Ppm\Core\Solution;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class UnpackPackagesToSolutionCommand extends CommandBase
{
    public function getDescription(): string
    {
        return "Unpack dependencies to solution directory";
    }

    public function execute(array $parameters, array $options, array $argv): void
    {
        Solution::getSolutionOrThrow()->unpackPackages();
    }
}