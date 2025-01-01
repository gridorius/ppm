<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Event\EventDispatcher;
use Ppm\Framework\Event\IEvent;
use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\Contracts\IStream;
use Ppm\Framework\Stream\ResourceStream;

class AsyncEvents
{
    private static ?ResourceStream $eventStream;

    private static StreamReadActionBind $bind;

    public static function init(ResourceStream $eventStream): void
    {
        static::$eventStream = $eventStream;
        static::$bind = StreamReadActionBind::create($eventStream, function (IStream $stream) {
            $eventSerialized = $stream->readLine();
            $event = unserialize(trim($eventSerialized));
            EventDispatcher::emit($event);
        });
    }

    public static function getBind(): StreamReadActionBind
    {
        return self::$bind;
    }

    public static function emit(IEvent $event): void
    {
        if (static::isConnected())
            static::$eventStream->writeLine(serialize($event));
    }

    public static function isConnected(): bool
    {
        return !is_null(static::$eventStream);
    }
}