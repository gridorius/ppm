<?php

namespace Terminal\CommandRouting;

class CommandRouteCommand extends CommandRouteBase
{
    private CommandBase $handler;

    public function setHandler(CommandBase $handler): CommandRouteBase
    {
        $this->handler = $handler;
        return $this;
    }

    protected function getHandler(): array
    {
        return [$this->handler, 'execute'];
    }
}