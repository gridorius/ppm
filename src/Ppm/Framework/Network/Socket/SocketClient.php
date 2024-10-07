<?php

namespace Ppm\Framework\Network\Socket;


use Exception;
use Ppm\Framework\Stream\ResourceStream;

class SocketClient extends ResourceStream {
    public function __construct(string $url, int $timeout = 0){
        $errorMessage = '';
        $errorCode = '';

        $resource = stream_socket_client($url, $errorCode, $errorMessage, $timeout);

        if($resource === false){
            throw new Exception($errorMessage, $errorCode);
        }

        parent::__construct($resource);
    }
}