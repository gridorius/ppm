<?php

namespace Ppm\Framework\Network\Socket;

use Exception;
use Ppm\Framework\Stream\Parallel\StreamReceiverBase;
use Ppm\Framework\Stream\ResourceStream;
use Ppm\Framework\Traits\Observable;

abstract class StreamSocketServer
{
    use Observable;
    const EVENT_ON_ITERATION = 'on_iteration';
    protected SocketReceiver $socketReceiver;

    /**
     * @var StreamReceiverBase[]
     */
    protected array $receivers = [];

    protected ParallelSocket $parallel;

    public function __construct()
    {
        $this->parallel = new ParallelSocket();
    }

    public function getReceivers(): array
    {
        return $this->receivers;
    }

    public function getReceiver(string $id): StreamReceiverBase
    {
        return $this->receivers[$id];
    }

    public function listen(string $host, int $port, int $timeoutSeconds = null, int $timeoutMicroseconds = null): void
    {
        $this->createServer($host, $port);

        while (true) {
            $this->handleInput($timeoutSeconds, $timeoutMicroseconds);
            $this->call(static::EVENT_ON_ITERATION, $this);
        }
    }

    protected function createServer(string $host, int $port): void
    {
        $socketServer = stream_socket_server(
            "tcp://{$host}:{$port}",
            $errorCode,
            $errorMessage,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
        );
        if ($errorMessage)
            throw new Exception("{$errorMessage} ({$errorCode})");
        $stream = new ResourceStream($socketServer);
        $stream->unblock();
        $this->socketReceiver = new SocketReceiver($stream, $this);
    }

    public function handleInput(int $seconds = null, int $microseconds = null): array
    {
        $this
            ->parallel
            ->setReceivers($this->getWorkReceivers())
            ->addReceiver($this->socketReceiver)
            ->handleInput($seconds, $microseconds);
    }

    public function acceptConnection(string $peerName, ResourceStream $connectionStream): void
    {
        $connectionReceiver = $this->onConnect($peerName, $connectionStream);
        if (!is_null($connectionReceiver))
            $this->receivers[uniqid('connection_')] = $connectionReceiver;
    }

    /**
     * @return StreamReceiverBase[]
     */
    abstract protected function getWorkReceivers(): array;

    abstract protected function onConnect(string $peer, ResourceStream $stream): ?StreamReceiverBase;
}