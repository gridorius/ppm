<?php

namespace Ppm\Core\Commands\Build;

use Ppm\Framework\Terminal\CommandRouting\CommandsConfigurationBase;
use Ppm\Framework\Terminal\CommandRouting\CommandsRouter;

class BuilderCommandsConfiguration extends CommandsConfigurationBase
{
    public function configure(CommandsRouter $router): void
    {
        $router->registerCommand("build package - <project> -", new BuildPackageCommand());
        $router->registerCommand("build template <name> ", new CreateTemplateCommand());
        $router->registerCommand("build - <project> -", new BuildCommand());
        $router->registerCommand("start - <script> -", new ExecuteScriptCommand());
    }
}