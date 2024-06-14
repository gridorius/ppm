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
        echo shell_exec($this->command);
    }
}