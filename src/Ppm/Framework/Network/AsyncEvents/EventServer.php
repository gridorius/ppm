<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Event\EventDispatcher;
use Ppm\Framework\Network\Socket\Socket;
use Ppm\Framework\Network\Socket\StreamSocketServer;
use Ppm\Framework\Stream\Async\MainCycle;
use Ppm\Framework\Stream\MessageProtocol\MessageReceiver;
use Ppm\Framework\Stream\MessageProtocol\MessageSender;

class EventServer extends StreamSocketServer
{
    private array $listeners;

    public function __construct(string $host, int $port, int $backlog = 100)
    {
        parent::__construct($host, $port, $backlog);
        $this->listeners = [];
    }

    public function emit(string $event): void
    {
        EventDispatcher::emit(unserialize($event));
        foreach ($this->listeners as $listener)
            MessageSender::wrap($listener->getStream())->send($event);
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
        $receiver = new MessageReceiver(
            function (MessageReceiver $receiver) use ($peerName) {
                if ($receiver->isAborted()) {
                    $this->deleteListener($peerName);
                } else
                    $this->emit($receiver->getMessage());
            }
        );
        MainCycle::watch((function () use ($socket, $receiver) {
            yield $socket;
            while (!$receiver->isAborted()) {
                $receiver->onReadyData($socket);
                yield $socket;
            }
        })());
    }
}