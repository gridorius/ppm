<?php

namespace Ppm\Builder\Configuration;

class ProjectInfo
{
    private string $name;
    private string $version;
    private ?string $author;
    private ?string $description;

    /**
     * @param string $name
     * @param string $version
     * @param string|null $author
     * @param string|null $description
     */
    public function __construct(string $name, string $version, ?string $author, ?string $description)
    {
        $this->name = $name;
        $this->version = $version;
        $this->author = $author;
        $this->description = $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}