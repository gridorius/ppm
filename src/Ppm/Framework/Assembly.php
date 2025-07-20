<?php

namespace Ppm\Framework;

use Ppm\Framework\Resources\Resources;

class Assembly
{
    private string $name;
    private string $path;
    private string $realPath;
    private string $directory;
    private array $manifest;

    private bool $loaded;

    public function __construct(string $name, string $directory)
    {
        $this->loaded = false;
        $this->name = $name;
        $this->path = "phar://{$name}";
        $this->realPath = $directory . DIRECTORY_SEPARATOR . $name . '.phar';
        $this->directory = $directory;
        $this->manifest = include $this->path . '/manifest.php';
    }

    public function getRealPath(): string
    {
        return $this->realPath;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function markAssLoaded(): void
    {
        $this->loaded = true;
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDeclaredTypes(): array
    {
        return $this->manifest['types'];
    }

    public function hasType(string $type): bool
    {
        return key_exists($type, $this->manifest['types']);
    }

    public function getTypePath(string $type): string
    {
        return $this->manifest['types'][$type];
    }

    public function preloadTypes(): void
    {
        foreach ($this->getDeclaredTypes() as $type => $path)
            class_exists($type);
    }

    public function includeScripts(): void
    {
        foreach ($this->getIncludes() as $path)
            require $path;
    }

    public function registerResources(): void
    {
        foreach ($this->getResources() as $name => $path)
            Resources::addResource($name, $path);
    }

    public function getResources(): array
    {
        return $this->manifest['resources'];
    }

    public function getIncludes(): array
    {
        return $this->manifest['includes'];
    }

    public function getDepends(): array
    {
        return $this->manifest['depends'];
    }
}