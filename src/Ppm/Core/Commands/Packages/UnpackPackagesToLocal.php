<?php

namespace Ppm\Core\Commands\Packages;

use Ppm\Core\Solution;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class UnpackPackagesToLocal extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        Solution::getSolutionOrThrow()->unpackPackages();
    }
}