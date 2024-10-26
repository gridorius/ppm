<?php

namespace Ppm\Framework\Stream;

class TmpStream extends ResourceStream
{
    public function __construct()
    {
        parent::__construct(tmpfile());
    }

    public function getPath(): string
    {
        return $this->getMetadata()['uri'];
    }

    public function getContent(): string
    {
        return file_get_contents($this->getPath());
    }
}