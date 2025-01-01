<?php

namespace Ppm\Packages\Common\Storage;

use Phar;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Packages\Common\Metadata;
use Ppm\Packages\Common\MetadataUtil;

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
        $directory->extractPhar(new Phar($this->path), [MetadataUtil::METADATA_FILE_NAME]);
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

    public function getPharName(): string
    {
        return pathinfo($this->path, PATHINFO_BASENAME);
    }
}