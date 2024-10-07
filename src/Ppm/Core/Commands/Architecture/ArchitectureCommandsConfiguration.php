<?php

namespace Ppm\Core\Commands\Architecture;

use Ppm\Framework\Terminal\CommandRouting\CommandsConfigurationBase;
use Ppm\Framework\Terminal\CommandRouting\CommandsRouter;

class ArchitectureCommandsConfiguration extends CommandsConfigurationBase
{
    public function configure(CommandsRouter $router): void
    {
        $router->registerCommand("init <name>", new InitializeCommand())
            ->setDescription("Initialize a PPM project");

        $router->registerCommand("create project <name>", new CreateProjectCommand())
            ->setDescription("Initialize a PPM project");
    }
}