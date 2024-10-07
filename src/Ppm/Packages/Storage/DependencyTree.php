<?php

namespace Ppm\Packages\Storage;

class DependencyTree
{
    private array $found;
    private array $notFound;

    public function __construct()
    {
        $this->found = [];
        $this->notFound = [];
    }

    public function addFound(string $name, string $version): static
    {
        $this->found[$name] = $version;
        return $this;
    }

    public function addNotFound(string $name, string $version): static
    {
        $this->notFound[$name] = $version;
        return $this;
    }

    public function getFound(): array
    {
        return $this->found;
    }

    public function hasFound(): bool
    {
        return !empty($this->found);
    }

    public function getNotFound(): array
    {
        return $this->notFound;
    }

    public function hasNotFound(): bool
    {
        return !empty($this->notFound);
    }
}