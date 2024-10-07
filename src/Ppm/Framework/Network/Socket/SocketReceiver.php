<?php

namespace Ppm\Framework\Network\Socket;

use Ppm\Framework\Stream\Parallel\StreamReceiverBase;
use Ppm\Framework\Stream\ResourceStream;

class SocketReceiver extends StreamReceiverBase
{
    private StreamSocketServer $server;

    public function __construct(ResourceStream $stream, StreamSocketServer $server)
    {
        parent::__construct($stream);
        $this->server = $server;
    }

    public function onReadyContent(): bool
    {
        $connectionResource = stream_socket_accept($this->stream->getResource(), -1, $peerName);
        $this->server->acceptConnection($peerName, new ResourceStream($connectionResource));
    }
}