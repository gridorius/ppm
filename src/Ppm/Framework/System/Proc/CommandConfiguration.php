<?php

namespace Ppm\Framework\System\Proc;

class CommandConfiguration
{
    const TARGET_PIPE = 'pipe';
    const TARGET_FILE = 'file';
    private array $descriptors;
    private array $command;

    public function __construct(string ...$command)
    {
        $this->command = $command;
        $this->descriptors = [];
        $this
            ->setPipeDescriptor(Descriptors::STDIN, 'r')
            ->setResourceDescriptor(Descriptors::STDOUT, STDOUT)
            ->setResourceDescriptor(Descriptors::STDERR, STDERR);
    }

    public function setResourceDescriptor(int $descriptor, $resource): CommandConfiguration
    {
        $this->descriptors[$descriptor] = $resource;
        return $this;
    }

    public function setPipeDescriptor(int $descriptor, string $mode): self
    {
        return $this->setDescriptor($descriptor, self::TARGET_PIPE, $mode);
    }

    public function setFileDescriptor(int $descriptor, string $path, $mode = 'a'): self
    {
        return $this->setDescriptor($descriptor, self::TARGET_FILE, $path, $mode);
    }

    public function setDescriptor(int $descriptor, ...$options): self
    {
        $this->descriptors[$descriptor] = $options;
        return $this;
    }

    public function getDescriptors(): array
    {
        return $this->descriptors;
    }

    public function getCommand(): array
    {
        return $this->command;
    }
}