<?php

namespace PPM\Commands;

use Builder\BuildManager;
use PPM\Commands\Contracts\CommandBase;
use Exception;
use Packages\PackageManager;
use Utils\PathUtils;

class Build extends CommandBase
{
    public function execute(array $argv)
    {
        $outDir = $argv[0] ?? getcwd().'/out';
        $buildDir = $argv[1] ?? getcwd();

        $currentDir = getcwd();
        if (empty($buildDir)) {
            $buildDir = $currentDir;
        } else {
            $buildDir = PathUtils::resolveRelativePath($currentDir, $buildDir);
        }

        $outDir = PathUtils::resolveRelativePath($currentDir, $outDir);
        $projPath = PathUtils::findProj($buildDir);

        $packageManager = new PackageManager();
        $packageManager->restore($projPath);
        $buildManager = new BuildManager($projPath);
        $buildManager->build($outDir);
        $packages = $buildManager->getScanner()->getPackages();
        $paths = $packageManager->getPackagesRecursive($packages);
        $packageManager->unpackPackages($paths, $outDir);
    }
}