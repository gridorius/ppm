<?php

namespace Ppm\Framework\Stream;

class FileResourceStream extends ResourceStream
{
    public const MODE_READ = 'r';
    public const MODE_WRITE = 'w';
    public const MODE_READ_AND_WRITE = 'a+';

    public function __construct($path, $mode = self::MODE_WRITE)
    {
        $resource = fopen($path, $mode);
        parent::__construct($resource);
    }

    public function lock(int $operation): void
    {
        flock($this->resource, $operation);
    }
}