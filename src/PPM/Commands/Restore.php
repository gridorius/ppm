<?php

namespace PPM\Commands;

use PPM\Commands\Contracts\CommandBase;
use Exception;
use Packages\PackageManager;
use Utils\PathUtils;

class Restore extends CommandBase
{

    public function execute(array $argv)
    {
        $restoreDir = $argv[0] ?? getcwd();

        if (empty($restoreDir))
            throw new Exception("Expected parameter build directory");

        $projPath = PathUtils::findProj($restoreDir);
        $packageManager = new PackageManager();
        $packageManager->restore($projPath);
    }
}