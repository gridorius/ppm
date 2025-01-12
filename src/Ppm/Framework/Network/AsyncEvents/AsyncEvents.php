<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Event\EventDispatcher;
use Ppm\Framework\Event\IEvent;
use Ppm\Framework\Network\Socket\SocketHostPortClient;
use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\MessageProtocol\StreamMessageProtocol;
use Ppm\Framework\Stream\MessageProtocol\StreamMessageProtocolWrapper;
use Ppm\Framework\Stream\ResourceStream;

class AsyncEvents
{
    private static ?ResourceStream $eventStream = null;

    private static StreamReadActionBind $bind;

    public static function init(string $host, int $port): void
    {
        static::$eventStream = new SocketHostPortClient($host, $port);
        static::$eventStream->unblock();
        $protocol = new StreamMessageProtocol(function (StreamMessageProtocol $protocol) {
            if ($protocol->isAborted()) {
                throw new \Exception('Event stream is aborted');
            } else {
                $event = unserialize($protocol->getMessage());
                EventDispatcher::emit($event);
            }
        });
        static::$bind = $protocol->createBind(static::$eventStream);
    }

    public static function getBind(): StreamReadActionBind
    {
        if (!static::isConnected())
            throw new \Exception("Failed get bind before initialization");
        return self::$bind;
    }

    public static function emit(IEvent $event): void
    {
        if (static::isConnected())
            StreamMessageProtocolWrapper::wrap(static::$eventStream)->send(serialize($event));
    }

    public static function isConnected(): bool
    {
        return !is_null(static::$eventStream);
    }
}