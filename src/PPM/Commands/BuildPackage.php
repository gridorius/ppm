<?php

namespace PPM\Commands;

use Packages\PackagesController;
use Terminal\CommandRouting\CommandBase;
use Utils\PathUtils;

class BuildPackage extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $buildDir = $parameters['build_directory'];
        $pathToProjectFile = PathUtils::findProj($buildDir);
        $packageController = new PackagesController();
        $packageBuilder = $packageController->getBuilder();
        $packageBuilder->build($pathToProjectFile);
    }
}