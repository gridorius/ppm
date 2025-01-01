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

    /**
     * return typed project files with .php extension
     *
     * @return array
     */
    public function getTypeFiles(): array
    {
        return $this->filterFiles($this->configuration);
    }

    /**
     * return moved project files from files section in configuration
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->filterFilesByFiltersArray($this->configuration->getFileFilters()->getFiles());
    }

    /**
     * return project resources files
     *
     * @return array
     */
    public function getResources(): array
    {
        return $this->filterFilesByFiltersArray($this->configuration->getFileFilters()->getResources());
    }

    /**
     * return project include files
     *
     * @return array
     */
    public function getIncludes(): array
    {
        return $this->filterFilesByFiltersArray($this->configuration->getFileFilters()->getIncludes());
    }

    /**
     * return filtered project files by FileFilter
     *
     * @param FileFilter $filter
     * @return array
     */
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
        $offset = $filter->getOffset();
        foreach ($projectFiles as $key => $path) {
            if (fnmatch($include, $path, FNM_NOESCAPE))
                $files[$key] = preg_replace("/(.[^\/]+\/)/", '', $path, $offset);
        }

        return $files;
    }

    /**
     * return filtered project files by many filters FileFilter[]
     *
     * @param array $filters
     * @return array
     */
    public function filterFilesByFiltersArray(array $filters): array
    {
        $files = [];
        foreach ($filters as $filter)
            foreach ($this->filterFiles($filter) as $realPath => $relativePath)
                $files[$realPath] = $relativePath;

        return $files;
    }
}