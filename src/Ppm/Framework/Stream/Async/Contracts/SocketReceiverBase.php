<?php

namespace Ppm\Framework\Stream\Async\Contracts;

use Ppm\Framework\Network\Socket\Socket;

abstract class SocketReceiverBase implements IStreamReceiver
{
    protected Socket $target;

    public function __construct(Socket $socket)
    {
        $this->target = $socket;
    }

    public function getTarget(): Socket
    {
        return $this->target;
    }
}