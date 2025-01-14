<?php

namespace Ppm\Framework\Terminal\CommandRouting;

use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandRouteBase;

class CommandRouteCommand extends CommandRouteBase
{
    private CommandBase $handler;

    public function __construct(Pattern $pattern, CommandBase $handler)
    {
        parent::__construct($pattern);
        $this->handler = $handler;
    }

    protected function execute(array $parameters, array $options, array $argv): void
    {
        call_user_func([$this->handler, 'execute'], $parameters, $options, $argv);
    }
}