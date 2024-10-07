<?php

namespace Ppm\Framework\Traits;

use Closure;

trait Observable
{
    protected array $___handlers = [];

    public function on(string $event, Closure $handler): void
    {
        $this->___handlers[$event][] = $handler;
    }

    public function getHandlers(string $event): array
    {
        return $this->___handlers[$event] ?? [];
    }

    protected function call(string $event, ...$args): void
    {
        foreach ($this->getHandlers($event) as $handler)
            call_user_func_array($handler, $args);
    }
}