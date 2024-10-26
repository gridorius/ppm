<?php

namespace Ppm\Framework\Network\Socket;

use Ppm\Framework\Stream\ResourceStream;

class Socket extends ResourceStream
{
    public function getName(): string
    {
        return stream_socket_get_name($this->resource, true);
    }

    public function send(string $data): void
    {
        stream_socket_sendto($this->resource, $data);
    }
}