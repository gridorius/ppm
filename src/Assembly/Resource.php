<?php

namespace Assembly;

class Resource
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContent(): string
    {
        return file_get_contents($this->path);
    }

    public function include()
    {
        return include $this->path;
    }
}