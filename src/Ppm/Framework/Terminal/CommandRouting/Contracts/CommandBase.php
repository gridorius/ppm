<?php

namespace Ppm\Framework\Terminal\CommandRouting\Contracts;

abstract class CommandBase
{
    protected array $options = [];

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getDescription(): string
    {
        return '';
    }

    abstract public function execute(array $parameters, array $options, array $argv): void;
}