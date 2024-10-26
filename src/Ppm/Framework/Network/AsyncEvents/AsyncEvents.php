<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Event\IEvent;
use Ppm\Framework\Stream\ResourceStream;

class AsyncEvents
{
    private static ?ResourceStream $eventStream;

    private static AsyncEventReceiver $receiver;

    public static function init(ResourceStream $eventStream): void
    {
        static::$eventStream = $eventStream;
        static::$receiver = new AsyncEventReceiver($eventStream);
    }

    public static function emit(IEvent $event): void
    {
        if (static::isConnected())
            static::$eventStream->writeLine(serialize($event));
    }

    public static function getReceiver(): AsyncEventReceiver
    {
        return static::$receiver;
    }

    public static function isConnected(): bool
    {
        return !is_null(static::$eventStream);
    }
}