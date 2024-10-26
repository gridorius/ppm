<?php

namespace Ppm\Framework\Stream\Async\Contracts;

use Ppm\Framework\Stream\ResourceStream;

abstract class StreamReceiverBase implements IStreamReceiver
{
    protected ResourceStream $target;

    public function __construct(ResourceStream $stream)
    {
        $this->target = $stream;
    }

    public function getTarget(): ResourceStream
    {
        return $this->target;
    }
}