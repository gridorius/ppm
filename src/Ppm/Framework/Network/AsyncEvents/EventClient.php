<?php

namespace Ppm\Framework\Network\AsyncEvents;

use Ppm\Framework\Network\Socket\SocketHostPortClient;

class EventClient extends SocketHostPortClient
{
    public function __construct(string $server)
    {
        [$host, $port] = explode(":", $server);
        parent::__construct($host, (int)$port, 10);
    }
}