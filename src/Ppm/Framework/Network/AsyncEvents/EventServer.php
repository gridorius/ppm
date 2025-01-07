<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Network\Socket\Socket;
use Ppm\Framework\Network\Socket\StreamSocketServer;
use Ppm\Framework\Stream\Async\AsyncStreamWatcher;
use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\Contracts\IStreamRead;

class EventServer extends StreamSocketServer
{
    private array $listeners;
    private AsyncStreamWatcher $reader;

    public function __construct(string $host, int $port)
    {
        parent::__construct($host, $port, 100, false);
        $this->listeners = [];
        $this->reader = new AsyncStreamWatcher();
    }

    public function listen(): void
    {
        while (true) {
            $this->acceptConnection(0.00001, [$this, 'onConnect']);
            $this
                ->reader
                ->setBindings($this->listeners)
                ->watch(0, 300);
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
        $this->listeners[$peerName] = StreamReadActionBind::create($socket, function (IStreamRead $stream) use ($peerName) {
            $eventData = $stream->readLine();
            if (empty($eventData)) {
                $this->deleteListener($peerName);
                $stream->close();
            } else
                $this->emit($eventData);
        });
    }
}