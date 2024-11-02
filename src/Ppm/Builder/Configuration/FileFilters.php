<?php

namespace Ppm\Builder\Configuration;

class FileFilters
{
    /** @var FileFilter[] */
    private array $files;
    /** @var FileFilter[] */
    private array $resources;
    /** @var FileFilter[] */
    private array $includes;

    public function __construct(array $files, array $resources, array $includes)
    {
        $this->files = array_map(function ($conf) {
            return new FileFilter($conf);
        }, $files);
        $this->resources = array_map(function ($conf) {
            return new FileFilter($conf);
        }, $resources);
        $this->includes = array_map(function ($conf) {
            return new FileFilter($conf);
        }, $includes);
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getResources(): array
    {
        return $this->resources;
    }

    public function getIncludes(): array
    {
        return $this->includes;
    }
}