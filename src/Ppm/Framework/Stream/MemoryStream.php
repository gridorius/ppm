<?php

namespace Ppm\Framework\Stream;

class MemoryStream extends ResourceStream
{
    public function __construct(string $mode)
    {
        parent::__construct(fopen('php://memory', $mode));
    }
}