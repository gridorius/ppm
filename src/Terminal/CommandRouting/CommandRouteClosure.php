<?php

namespace Terminal\CommandRouting;

use Closure;

class CommandRouteClosure extends CommandRouteBase
{
    private Closure $handler;

    public function setHandler(Closure $handler): CommandRouteBase
    {
        $this->handler = $handler;
        return $this;
    }

    protected function getHandler(): Closure
    {
        return $this->handler;
    }
}