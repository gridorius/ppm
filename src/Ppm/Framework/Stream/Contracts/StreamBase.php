<?php

namespace Ppm\Framework\Stream\Contracts;

abstract class StreamBase implements IStream
{
    public function hasContent(): bool
    {
        return !$this->isValid() && $this->getRemainderLength() > 0;
    }
}