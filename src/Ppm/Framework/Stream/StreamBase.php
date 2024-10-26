<?php

namespace Ppm\Framework\Stream;

abstract class StreamBase extends ResourceBase implements IStream
{
    public function hasContent(): bool
    {
        return !$this->isValid() && $this->getRemainderLength() > 0;
    }
}