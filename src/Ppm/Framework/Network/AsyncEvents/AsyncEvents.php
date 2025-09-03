<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Exception;
use Ppm\Framework\Event\EventDispatcher;
use Ppm\Framework\Event\IEvent;
use Ppm\Framework\Network\Socket\SocketHostPortClient;
use Ppm\Framework\Stream\Async\MainCycle;
use Ppm\Framework\Stream\MessageProtocol\MessageReceiver;
use Ppm\Framework\Stream\MessageProtocol\MessageSender;
use Ppm\Framework\Stream\ResourceStream;

class AsyncEvents
{
    private static ?ResourceStream $eventStream = null;
    private static ?MessageReceiver $receiver = null;

    public static function init(string $host, int $port): void
    {
        static::$eventStream = new SocketHostPortClient($host, $port);
        static::$eventStream->unblock();
        static::$receiver = new MessageReceiver(function (MessageReceiver $receiver) {
            if ($receiver->isAborted()) {
                throw new Exception('Event stream is aborted');
            } else {
                $event = unserialize($receiver->getMessage());
                EventDispatcher::emit($event);
            }
        });
        MainCycle::watch((function () {
            yield static::$eventStream;
            while (!static::$receiver->isAborted()) {
                static::$receiver->onReadyData(static::$eventStream);
                yield static::$eventStream;
            }
        })());
    }


    public static function emit(IEvent $event): void
    {
        if (static::isConnected())
            MessageSender::wrap(static::$eventStream)->send(serialize($event));
    }

    public static function isConnected(): bool
    {
        return !is_null(static::$eventStream);
    }
}