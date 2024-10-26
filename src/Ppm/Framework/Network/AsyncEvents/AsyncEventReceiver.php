<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Event\EventDispatcher;
use Ppm\Framework\Stream\Async\Contracts\StreamReceiverBase;

class AsyncEventReceiver extends StreamReceiverBase
{
    public function onReadyContent(): void
    {
        $eventSerialized = $this->target->readLine();
        $event = unserialize(trim($eventSerialized));
        EventDispatcher::emit($event);
    }
}