<?php

namespace PPM\Commands;

use PPM\Commands\Contracts\CommandBase;
use Exception;
use Packages\PackageManager;
use Utils\PathUtils;

class UploadPackage extends CommandBase
{
    public function execute(array $argv)
    {
        if (empty($argv[0]))
            throw new Exception("Expected parameter source");
        $source = $argv[0];
        $projectDir = $argv[1] ?? getcwd();
        $proj = PathUtils::findProj($projectDir);
        $configuration = PathUtils::getJson($proj);
        $manager = new PackageManager();
        $path = $manager->getLocal()->findPackage($configuration['name'], $configuration['version']);

        if(is_null($path))
            throw new Exception("Package {$configuration['name']}:{$configuration['version']} dont built");

        $manager->getRemote()->uploadPackage($path, $source);
    }
}