<?php

namespace Ppm\Framework\System\Proc;

use Ppm\Framework\System\Proc\Descriptors\DescriptorBase;
use Ppm\Framework\System\Proc\Descriptors\PipeDescriptor;
use Ppm\Framework\System\Proc\Descriptors\STDDescriptor;

class CommandConfiguration
{
    const TARGET_PIPE = 'pipe';
    const TARGET_FILE = 'file';
    protected array $descriptors;
    protected array $command;

    public function __construct(string ...$command)
    {
        $this->command = $command;
        $this->descriptors = [];
        $this
            ->setDescriptor(Descriptors::STDIN, new PipeDescriptor('r'))
            ->setDescriptor(Descriptors::STDOUT, new STDDescriptor(Descriptors::STDOUT))
            ->setDescriptor(Descriptors::STDERR, new STDDescriptor(Descriptors::STDERR));
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

    public function clone(): static
    {
        $command = new static(...$this->command);
        foreach ($this->descriptors as $key => $value)
            $command->setDescriptor($key, $value);
        return $command;
    }

    public function setDescriptor(int $descriptor, DescriptorBase $type): self
    {
        $this->descriptors[$descriptor] = $type;
        return $this;
    }

    public function getDescriptors(): array
    {
        return array_map(function (DescriptorBase $descriptor) {
            return $descriptor->getDescriptor();
        }, $this->descriptors);
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