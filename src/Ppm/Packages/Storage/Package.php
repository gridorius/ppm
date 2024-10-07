<?php

namespace Ppm\Packages\Storage;

use Phar;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Packages\Metadata;
use Ppm\Packages\MetadataUtil;

class Package
{
    private string $path;
    private Metadata $metadata;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function extractTo(Directory $directory): void
    {
        $directory->extractPhar(new Phar($this->path));
    }

    public function getMetadata(): Metadata
    {
        if (empty($this->metadata))
            $this->metadata = MetadataUtil::getPackageMetadata($this->path);

        return $this->metadata;
    }

    public function getDepends(): array
    {
        return $this->getMetadata()->getDepends();
    }

    public function getPath(): string
    {
        return $this->path;
    }
}