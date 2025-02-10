<?php

namespace Ppm\Framework\Stream\Async;

use Closure;

class Task
{
    private bool $running;
    private bool $everyTime;
    private Closure $action;

    public function __construct(Closure $action, bool $everyTime = false)
    {
        $this->action = $action;
        $this->everyTime = $everyTime;
        $this->running = true;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function isEveryTime(): bool
    {
        return $this->everyTime;
    }

    public function shutdown(): void
    {
        $this->running = false;
    }

    public function run(): void
    {
        MainCycle::addTask($this);
    }

    public function call(): void
    {
        call_user_func($this->action, $this);
    }
}