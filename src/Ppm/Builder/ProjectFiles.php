<?php

namespace Ppm\Builder;

use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\FileFilter;

class ProjectFiles
{
    private array $files;
    private Configuration $configuration;

    /**
     * @param array $files
     * @param Configuration $configuration
     */
    public function __construct(array $files, Configuration $configuration)
    {
        $this->files = $files;
        $this->configuration = $configuration;
    }

    public function getTypeFiles(): array
    {
        return $this->filterFiles($this->configuration);
    }

    public function getFiles(): array
    {
        return $this->filterFilesByFiltersArray($this->configuration->getFiles());
    }

    public function getResources(): array
    {
        return $this->filterFilesByFiltersArray($this->configuration->getResources());
    }

    public function getIncludes(): array
    {
        return $this->filterFilesByFiltersArray($this->configuration->getIncludes());
    }


    public function filterFiles(FileFilter $filter): array
    {
        $projectFiles = $this->files;
        $files = [];

        if ($filter->hasExclude()) {
            $excludeArray = explode(';', $filter->getExclude());
            foreach ($excludeArray as $pattern) {
                foreach ($projectFiles as $key => $path) {
                    if (fnmatch($pattern, $path, FNM_NOESCAPE))
                        unset($projectFiles[$key]);
                }
            }
        }

        $include = $filter->getInclude();
        foreach ($projectFiles as $key => $path) {
            if (fnmatch($include, $path, FNM_NOESCAPE))
                $files[$key] = $path;
        }

        return $files;
    }

    public function filterFilesByFiltersArray(array $filters): array
    {
        $files = [];
        foreach ($filters as $filter)
            foreach ($this->filterFiles($filter) as $realPath => $relativePath)
                $files[$realPath] = $relativePath;

        return $files;
    }
}