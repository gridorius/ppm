<?php

namespace Ppm\Builder;

use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\FileFilter;
use Ppm\Framework\Filesystem\PathUtils;

class ProjectFiles
{
    private string $directory;
    private array $files = [];
    private array $hashes = [];
    private Configuration $configuration;

    public function __construct(string $directory, Configuration $configuration, array $files = [], array $hashes = [])
    {
        $this->directory = $directory;
        $this->configuration = $configuration;
        $this->files = $files;
        $this->hashes = $hashes;
    }

    public function fromChanged(array $changed): static
    {
        $changedFiles = [];
        $hashes = [];
        foreach ($changed as $relativePath)
            if (key_exists($relativePath, $this->files)) {
                $changedFiles[$relativePath] = $this->files[$relativePath];
                $hashes[$relativePath] = $this->hashes[$relativePath];
            }
        return new static($this->directory, $this->configuration, $changedFiles, $hashes);
    }

    public function scan(): static
    {
        $this->hashes = [];
        $this->files = $this->separateProjects(PathUtils::scanDirectory($this->directory));
        foreach ($this->files as $relative => $full) {
            $hash = hash_file('sha256', $full);
            if (is_string($hash))
                $this->hashes[$relative] = $hash;
        }
        return $this;
    }

    public function getHashes(): array
    {
        return $this->hashes;
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
        $projectFiles = array_flip($this->files);
        $files = [];

        if ($filter->hasExclude()) {
            $excludeArray = explode(';', $filter->getExclude());
            foreach ($excludeArray as $pattern)
                foreach ($projectFiles as $key => $path)
                    if (fnmatch($pattern, $path, FNM_NOESCAPE))
                        unset($projectFiles[$key]);
        }

        $include = $filter->getInclude();
        $offset = $filter->getOffset();
        foreach ($projectFiles as $key => $path)
            if (fnmatch($include, $path, FNM_NOESCAPE))
                $files[$key] = preg_replace("/(.[^\/]+\/)/", '', $path, $offset);

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

    private function separateProjects(array $files): array
    {
        $subProjectRoots = [];
        foreach ($files as $relative => $absolute) {
            if (fnmatch('*proj.json', $relative)) {
                $dirname = pathinfo($relative, PATHINFO_DIRNAME);
                if ($dirname != '.')
                    $subProjectRoots[] = $dirname;
            }
        }
        $unsetFiles = [];
        foreach ($subProjectRoots as $prefix)
            foreach ($files as $relative => $absolute)
                if (str_starts_with($relative, $prefix))
                    $unsetFiles[] = $relative;

        foreach ($unsetFiles as $relative)
            unset($files[$relative]);

        return $files;
    }
}