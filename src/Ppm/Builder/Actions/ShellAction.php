<?php

namespace Ppm\Builder\Actions;

class ShellAction extends ActionBase
{
    private string $command;

    public function __construct(string $command)
    {
        $this->command = $command;
    }

    public function run(): void
    {
        $command = $this->prepareString($this->command);
        proc_open($command, [
            1 => STDOUT,
            2 => STDERR,
        ], $pipes);
    }
}