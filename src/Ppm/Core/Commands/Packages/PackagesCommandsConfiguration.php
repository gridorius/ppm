<?php

namespace Ppm\Core\Commands\Packages;

use Ppm\Framework\Terminal\CommandRouting\CommandsConfigurationBase;
use Ppm\Framework\Terminal\CommandRouting\CommandsRouter;

class PackagesCommandsConfiguration extends CommandsConfigurationBase
{
    public function configure(CommandsRouter $router): void
    {
        $router->registerCommand("packages unpack", new UnpackPackagesToLocal());
        $router->registerCommand("packages upload <source> <name> <version>", new UploadPackage());
    }
}