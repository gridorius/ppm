<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Network\Socket\Socket;
use Ppm\Framework\Stream\Async\Contracts\SocketReceiverBase;

class EventListenerReceiver extends SocketReceiverBase
{
    private string $peer;
    private EventServer $server;

    public function __construct(Socket $socket, string $peer, EventServer $server)
    {
        parent::__construct($socket);
        $this->peer = $peer;
        $this->server = $server;
    }

    public function emit(string $event): void
    {
        $this->target->write($event);
    }

    public function onReadyContent(): void
    {
        $eventData = $this->target->readLine();
        if (empty($eventData)) {
            $this->server->deleteListener($this->peer);
            $this->target->close();
        } else
            $this->server->emit($eventData);
    }
}