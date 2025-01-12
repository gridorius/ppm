<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Event\EventDispatcher;
use Ppm\Framework\Network\Socket\Socket;
use Ppm\Framework\Network\Socket\StreamSocketServer;
use Ppm\Framework\Stream\Async\AsyncStreamWatcher;
use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\Contracts\IStream;
use Ppm\Framework\Stream\Contracts\IStreamRead;
use Ppm\Framework\Stream\MessageProtocol\StreamMessageProtocol;
use Ppm\Framework\Stream\MessageProtocol\StreamMessageProtocolWrapper;

class EventServer extends StreamSocketServer
{
    private array $listeners;

    public function __construct(string $host, int $port, int $backlog = 100)
    {
        parent::__construct($host, $port, $backlog);
        $this->listeners = [];
    }

    public function listen(): void
    {
        $watcher = new AsyncStreamWatcher();
        while (true) {
            $this->iteration();
            $watcher
                ->setBindings($this->listeners)
                ->watch(0, 300);
        }
    }

    public function emit(string $event): void
    {
        EventDispatcher::emit(unserialize($event));
        foreach ($this->listeners as $listener)
            StreamMessageProtocolWrapper::wrap($listener->getStream())->send($event);
    }

    public function deleteListener(string $peer): void
    {
        unset($this->listeners[$peer]);
    }

    public function getBindings(): array
    {
        return $this->listeners;
    }

    public function iteration(): void
    {
        $this->acceptConnection(0.00001, [$this, 'onConnect']);
    }

    public function onConnect(string $peerName, Socket $socket): void
    {
        $protocol = new StreamMessageProtocol(
            function (StreamMessageProtocol $protocol) use ($peerName) {
                if ($protocol->isAborted()) {
                    $this->deleteListener($peerName);
                } else
                    $this->emit($protocol->getMessage());
            }
        );
        $this->listeners[$peerName] = $protocol->createBind($socket);
    }
}