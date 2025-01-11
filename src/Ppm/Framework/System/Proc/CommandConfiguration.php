<?php

namespace Ppm\Framework\System\Proc;

use Ppm\Framework\System\Proc\Descriptors\DescriptorBase;
use Ppm\Framework\System\Proc\Descriptors\PipeDescriptor;
use Ppm\Framework\System\Proc\Descriptors\StandartDescriptor;

class CommandConfiguration
{
    /**
     * @var DescriptorBase[]
     */
    protected array $descriptors;
    protected array $command;

    public function __construct(string ...$command)
    {
        $this->command = $command;
        $this->descriptors = [];
        $this
            ->setDescriptor(Descriptors::STDIN, new PipeDescriptor('r'))
            ->setDescriptor(Descriptors::STDOUT, new StandartDescriptor(Descriptors::STDOUT))
            ->setDescriptor(Descriptors::STDERR, new StandartDescriptor(Descriptors::STDERR));
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

    public function setDescriptor(int $descriptor, DescriptorBase $type): self
    {
        $this->descriptors[$descriptor] = $type;
        return $this;
    }

    public function configureDescriptors(array &$descriptors): void
    {
        foreach ($this->descriptors as $descriptor)
            $descriptor->configureDescriptor($descriptors);
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