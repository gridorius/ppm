<?php

namespace Ppm\Core\Commands;

use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class InstallExtensionCommand extends CommandBase
{
    public function execute(array $parameters, array $options, array $argv): void
    {
        $name = $parameters["package"];
        $version = $parameters["version"];

        $packageManager = new PackagesManager();
        $packageManager->getRestoreService()->restore([
            $name => $version
        ]);
        $package = $packageManager->getStorage()->find($name, $version);
        $package->extractTo($packageManager->getExtensionsDirectory());
    }
}