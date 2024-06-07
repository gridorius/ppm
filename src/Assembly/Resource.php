<?php

namespace Assembly;

class Resource
{
    protected string $name;
    protected string $path;

    public function __construct(string $name, string $path)
    {
        $this->name = $name;
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