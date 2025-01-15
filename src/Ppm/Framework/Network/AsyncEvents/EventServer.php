<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Event\EventDispatcher;
use Ppm\Framework\Network\Socket\Socket;
use Ppm\Framework\Network\Socket\StreamSocketServer;
use Ppm\Framework\Stream\Async\AsyncStreamWatcher;
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

    public function listen(): void
    {
        $watcher = new AsyncStreamWatcher();
        while (true) {
            $this->iteration();
            $watcher
                ->setBindings($this->getBindings())
                ->watch(0, 300);
        }
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
        $this->listeners[$peerName] = $receiver->createBind($socket);
    }
}