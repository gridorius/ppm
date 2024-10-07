<?php

namespace Ppm\Framework\Filesystem;

abstract class FromPath
{
    protected string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public static function from(string $path): static
    {
        return new static($path);
    }
}