<?php

namespace Ppm\Framework\Network\Socket;

class SocketHostPortClient extends SocketClient
{
    public function __construct(string $host, int $port, int $timeout = 1)
    {
        parent::__construct("tcp://{$host}:{$port}", $timeout);
    }
}