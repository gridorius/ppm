<?php

namespace Terminal\CommandRouting;

abstract class CommandBase
{
    protected array $options = [];

    public function getOptions(): array
    {
        return $this->options;
    }

    abstract public function execute(array $parameters, array $options): void;
}