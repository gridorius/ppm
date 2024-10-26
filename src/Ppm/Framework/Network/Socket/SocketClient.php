<?php

namespace Ppm\Framework\Network\Socket;


use Exception;
use Ppm\Framework\Network\Socket\Exceptions\TimeoutException;
use Ppm\Framework\Stream\ResourceStream;

class SocketClient extends ResourceStream
{
    public function __construct(string $url, int $timeout = 0)
    {
        $resource = stream_socket_client($url, $errorCode, $errorMessage, $timeout);

        if ($resource === false) {
            switch ($errorCode) {
                case 110:
                    throw new TimeoutException($errorMessage, $errorCode);
                default:
                    throw new Exception($errorMessage, $errorCode);
            }
        }

        parent::__construct($resource);
    }
}