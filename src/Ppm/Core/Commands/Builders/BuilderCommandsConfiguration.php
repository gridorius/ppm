<?php

namespace Ppm\Core\Commands\Builders;

use Ppm\Framework\Terminal\CommandRouting\CommandsConfigurationBase;
use Ppm\Framework\Terminal\CommandRouting\CommandsRouter;

class BuilderCommandsConfiguration extends CommandsConfigurationBase
{
    public function configure(CommandsRouter $router): void
    {
        $router->registerCommand("build package - <project> -", new BuildPackage());
        $router->registerCommand("build - <project> -", new Build());
        $router->registerCommand("restore <project>", new Restore());
    }
}