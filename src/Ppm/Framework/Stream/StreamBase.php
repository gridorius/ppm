<?php

namespace Ppm\Framework\Stream;

abstract class StreamBase extends ResourceBase implements IStream
{
    public function readAll(): string
    {
        return $this->read($this->getRemainderLength());
    }

    public function readLine(): string
    {
        return $this->readToChar("\n");
    }

    public function hasContent(): bool
    {
        return !$this->isValid() && $this->getRemainderLength() > 0;
    }
}