<?php

namespace Ppm\Framework\Event;

use Closure;

class EventDispatcher
{
    private array $handlers = [];

    public function addEventHandler(string $eventName, callable $handler): void
    {
        $this->handlers[$eventName][] = $handler;
    }

    public function emit(IEvent $event): void
    {
        foreach ($this->handlers[$event->getName()] as $handler)
            call_user_func($handler, $event);
    }
}