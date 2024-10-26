<?php

namespace Ppm\Framework\Event;

use Closure;

class EventDispatcher
{
    private static array $handlers = [];

    public static function addEventHandler(string $eventName, callable $handler): void
    {
        static::$handlers[$eventName][] = $handler;
    }

    public static function emit(IEvent $event): void
    {
        if (!empty(static::$handlers[$event->getName()]))
            foreach (static::$handlers[$event->getName()] as $handler)
                call_user_func($handler, $event);
    }
}