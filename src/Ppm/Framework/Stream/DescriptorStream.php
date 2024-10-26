<?php

namespace Ppm\Framework\Stream;

class DescriptorStream extends ResourceStream
{
    public function __construct(int $descriptor, $mode = Modes::MODE_WRITE)
    {
        parent::__construct(fopen("php://fd/{$descriptor}", $mode));
    }
}