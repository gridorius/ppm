<?php

namespace PPM\Commands;

use Packages\PackagesController;
use PPM\Commands\Contracts\CommandBase;
use Utils\PathUtils;

class BuildPackage extends CommandBase
{
    public function execute(array $argv)
    {
        $buildDir = $argv[0] ?? getcwd();
        $pathToProjectFile = PathUtils::findProj($buildDir);
        $packageController = new PackagesController();
        $packageBuilder = $packageController->getBuilder();
        $packageBuilder->build($pathToProjectFile);
    }
}