<?php

namespace Ppm\Framework\System\Proc;

class CommandConfigurationString extends CommandConfigurationBase
{
    protected string $command;

    public function __construct(string $command)
    {
        $this->command = $command;
        parent::__construct();
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}