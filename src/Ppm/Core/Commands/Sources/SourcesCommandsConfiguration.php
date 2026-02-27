<?php

namespace Ppm\Core\Commands\Sources;


use Ppm\Framework\Terminal\CommandRouting\CommandsConfigurationBase;
use Ppm\Framework\Terminal\CommandRouting\CommandsRouter;

class SourcesCommandsConfiguration extends CommandsConfigurationBase
{
    public function configure(CommandsRouter $router): void
    {
        $router->registerCommand("sources list", new SourceListCommand());
        $router->registerCommand("sources add <source> [alias]", new AddSourceCommand());
        $router->registerCommand("sources delete <source>", new DeleteSourceCommand());
        $router->registerCommand("auth <source> <login> [alias]", new AuthCommand());
    }
}