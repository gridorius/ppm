<?php

namespace Ppm\Framework\Terminal\CommandRouting;

use Closure;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandRouteBase;

class CommandRouteClosure extends CommandRouteBase
{
    private Closure $handler;

    public function __construct(Pattern $pattern, callable $handler)
    {
        parent::__construct($pattern);
        $this->handler = Closure::fromCallable($handler);
    }

    protected function execute(array $parameters, array $options, array $argv): void
    {
        call_user_func($this->handler, $parameters, $options, $argv);
    }
}