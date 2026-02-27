<?php

namespace Ppm\Framework\Event;

use ReflectionEnum;
use UnitEnum;

class EventDispatcher
{
    private static array $handlers = [];

    public static function addEventHandler(UnitEnum $case, callable $handler): void
    {
        static::$handlers[$case::class . '::' . $case->name][] = $handler;
    }

    public static function emit(UnitEnum $case, &...$arguments): bool
    {
        $eventName = $case::class . '::' . $case->name;
        if (!empty(static::$handlers[$eventName]))
            foreach (static::$handlers[$eventName] as $handler) {
                $result = call_user_func_array($handler, $arguments);
                if (is_bool($result) && !$result)
                    return false;
            }
        return true;
    }
}