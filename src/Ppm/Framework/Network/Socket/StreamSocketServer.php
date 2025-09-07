<?php

namespace Ppm\Framework\Network\Socket;

use Exception;
use Ppm\Framework\Event\EventDispatcher;
use Ppm\Framework\Network\Socket\Events\ServerEvents;
use Ppm\Framework\Stream\ResourceStream;

class StreamSocketServer extends ResourceStream
{
    public function __construct(string $host, int $port, int $backlog = 500, bool $reusePort = false, ?SSLOptions $sslOptions = null)
    {
        $transport = empty($sslOptions) ? 'tcp' : 'ssl';
        $contextOptions = [
            'socket' => [
                'backlog' => $backlog,
                'so_reuseport' => $reusePort
            ]
        ];
        if (!empty($sslOptions))
            $contextOptions['ssl'] = $sslOptions->toArray();
        $server = stream_socket_server(
            "{$transport}://{$host}:{$port}",
            $errorCode,
            $errorMessage,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            stream_context_create($contextOptions)
        );

        parent::__construct($server);
        if (!empty($errorMessage))
            throw new Exception($errorMessage, $errorCode);
        $this->unblock();
        EventDispatcher::emit(ServerEvents::Created, $host, $port);
    }

    public function acceptConnection(float $timeout, callable $callback): void
    {
        $newConnectionStream = stream_socket_accept($this->resource, $timeout, $peerName);
        if ($newConnectionStream === false)
            return;
        $connection = new Socket($newConnectionStream);
        $connection->unblock();
        $callback($peerName, $connection);
    }

    public static function from($resource): static
    {
        throw new Exception("Not implemented");
    }
}