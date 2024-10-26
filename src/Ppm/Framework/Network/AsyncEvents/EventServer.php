<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Network\Socket\Socket;
use Ppm\Framework\Network\Socket\StreamSocketServer;
use Ppm\Framework\Stream\Async\AsyncStreamsReader;

class EventServer extends StreamSocketServer
{
    private array $listeners;
    private AsyncStreamsReader $reader;

    public function __construct(string $host, int $port)
    {
        parent::__construct($host, $port, 100, false);
        $this->listeners = [];
        $this->reader = new AsyncStreamsReader();
    }

    public function listen(): void
    {
        while (true) {
            $this->acceptConnection(0.00001, [$this, 'onConnect']);
            $this
                ->reader
                ->setReceivers($this->listeners)
                ->awaitContent(0, 300);
        }
    }

    public function emit(string $event): void
    {
        foreach ($this->listeners as $listener)
            $listener->emit($event);
    }

    public function deleteListener(string $peer): void
    {
        unset($this->listeners[$peer]);
    }

    public function onConnect(string $peerName, Socket $socket): void
    {
        $this->listeners[$peerName] = new EventListenerReceiver($socket, $peerName, $this);
    }
}