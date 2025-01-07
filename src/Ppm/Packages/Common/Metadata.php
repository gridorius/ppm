<?php

namespace Ppm\Packages\Common;

class Metadata
{
    private string $name;
    private string $version;
    private string $author;
    private string $description;
    private array $depends;
    private array $hashes;
    private string $hashSum;

    public function __construct(array $metadata)
    {
        $this->name = $metadata['name'];
        $this->version = $metadata['version'];
        $this->author = $metadata['author'] ?? '';
        $this->description = $metadata['description'] ?? '';
        $this->depends = $metadata['depends'];
        $this->hashes = $metadata['hashes'];
        $this->hashSum = $metadata['hashSum'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDepends(): array
    {
        return $this->depends;
    }

    public function getHashes(): array
    {
        return $this->hashes;
    }

    public function getHashSum(): string
    {
        return $this->hashSum;
    }
}