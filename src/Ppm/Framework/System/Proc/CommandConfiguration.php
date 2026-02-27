<?php

namespace Ppm\Framework\System\Proc;

use Ppm\Framework\System\Proc\Descriptors\DescriptorBase;
use Ppm\Framework\System\Proc\Descriptors\PipeDescriptor;
use Ppm\Framework\System\Proc\Descriptors\StandartDescriptor;

class CommandConfiguration extends CommandConfigurationBase
{
    protected array $command;

    public function __construct(string ...$command)
    {
        $this->command = $command;
        parent::__construct();
    }

    public function addArguments(string ...$arguments): static
    {
        $this->command = array_merge($this->command, $arguments);
        return $this;
    }

    public function setCommand(string ...$command): static
    {
        $this->command = $command;
        return $this;
    }

    public function getCommand(): array
    {
        return $this->command;
    }

    public function getCommandString(): string
    {
        return implode(' ', $this->command);
    }
}