<?php

namespace Ppm\Framework\Stream\Parallel;

use Ppm\Framework\Stream\IStream;
use Ppm\Framework\Stream\ResourceStream;

abstract class StreamReceiverBase
{
    protected IStream $stream;

    public function __construct(ResourceStream $stream)
    {
        $this->stream = $stream;
    }

    public function getStream(): ResourceStream
    {
        return $this->stream;
    }

    /**
     * @return bool content available
     */
    abstract public function onReadyContent(): bool;
}