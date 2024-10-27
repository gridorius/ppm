<?php

namespace Ppm\Framework\Filesystem;

class DirectoryStorage
{
    private Directory $directory;

    public function __construct(string $path)
    {
        $this->directory = Directory::from($path)->create();
    }

    public function clear(): void
    {
        $this->directory->clear();
    }

    public function get(string $name): File
    {
        return File::from($this->directory->combine($name));
    }

    public function getSection(string $path): DirectoryStorage
    {
        return new DirectoryStorage($this->directory->combine($path));
    }
}