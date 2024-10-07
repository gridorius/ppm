<?php

namespace Ppm\Core\Commands\Sources;


use Ppm\Framework\Terminal\CommandRouting\CommandsConfigurationBase;
use Ppm\Framework\Terminal\CommandRouting\CommandsRouter;

class SourcesCommandsConfiguration extends CommandsConfigurationBase
{
    public function configure(CommandsRouter $router): void
    {
        $router->registerCommand("sources list", new SourceList());
        $router->registerCommand("sources add <source> [alias]", new AddSource());
        $router->registerCommand("sources delete <source>", new DeleteSource());
        $router->registerCommand("auth <source> <login> [alias]", new Auth());
    }
}