<?php

namespace Terminal\CommandRouting;

class CommandRouteCommand extends CommandRouteBase
{
    private CommandBase $handler;

    public function __construct(array $matches, string $pattern, CommandBase $handler)
    {
        parent::__construct($matches, $pattern);
        $this->handler = $handler;
    }

    protected function getHandler(): array
    {
        return [$this->handler, 'execute'];
    }
}