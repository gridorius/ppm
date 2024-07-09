<?php

namespace Builder\Actions;

class ShellAction implements IAction
{
    private string $command;

    public function __construct(string $command)
    {
        $this->command = $command;
    }

    public function setDirectories(string $buildDirectory, string $outDirectory): void
    {
        $this->command = ActionReplaceUtils::replacePaths($buildDirectory, $outDirectory, $this->command);
    }

    public function run(): void
    {
        proc_open($this->command, [
            1 => STDOUT,
            2 => STDERR,
        ], $pipes);
    }
}