<?php

namespace Ppm\Framework\Terminal\CommandRouting;

abstract class CommandsConfigurationBase
{
    abstract public function configure(CommandsRouter $router): void;
}