<?php

namespace Terminal\CommandRouting;

use Closure;

class CommandRouteClosure extends CommandRouteBase
{
    private Closure $handler;

    public function __construct(array $matches, string $pattern, Closure $handler)
    {
        parent::__construct($matches, $pattern);
        $this->handler = $handler;
    }

    protected function getHandler(): Closure
    {
        return $this->handler;
    }
}