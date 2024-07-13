<?php

namespace PPM\Commands;

use Packages\PackagesController;
use Terminal\CommandRouting\CommandBase;
use Utils\PathUtils;

class BuildPackage extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $buildDir = $parameters['build_directory'] ?? getcwd();
        $packageController = new PackagesController();
        $packageBuilder = $packageController->getBuilder();
        if (empty($options['r'])) {
            $pathToProjectFile = PathUtils::findProj($buildDir);
            $packageBuilder->build($pathToProjectFile);
        } else {
            $packageBuilder->buildResourcesPackage($buildDir);
        }
    }
}