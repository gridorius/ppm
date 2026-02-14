<?php

namespace Ppm\Builder;

use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\FileFilter;
use Ppm\Builder\Configuration\Manifest;
use Ppm\Framework\Filesystem\PathUtils;

class ProjectFiles
{
    private string $directory;
    private array $projectFiles = [];
    private array $hashes = [];
    private array $types = [];
    private array $files = [];
    private array $resources = [];
    private array $includes = [];
    private Configuration $configuration;

    public function __construct(string $directory, Configuration $configuration, array $projectFiles = [])
    {
        $this->directory = $directory;
        $this->configuration = $configuration;
        $this->setProjectFiles($projectFiles);
    }

    public function removeUnchanged(array $changed): static
    {
        foreach ($this->files as $real => $relative)
            if (!in_array($relative, $changed))
                unset($this->files[$real]);
        foreach ($this->types as $real => $relative)
            if (!in_array($relative, $changed))
                unset($this->types[$real]);
        foreach ($this->resources as $real => $relative)
            if (!in_array($relative, $changed))
                unset($this->resources[$real]);
        foreach ($this->includes as $real => $relative)
            if (!in_array($relative, $changed))
                unset($this->includes[$real]);
        return $this;
    }

    public function scan(): static
    {
        $this->hashes = [];
        $this->setProjectFiles($this->separateProjects(PathUtils::scanDirectory($this->directory)));
        return $this;
    }

    public function setProjectFiles(array $projectFiles): static
    {
        $this->projectFiles = $projectFiles;
        if (empty($projectFiles))
            return $this;
        $this->types = $this->filterFiles($this->configuration);
        $this->files = $this->filterFilesByFiltersArray($this->configuration->getFileFilters()->getFiles());
        $this->resources = $this->filterFilesByFiltersArray($this->configuration->getFileFilters()->getResources());
        $this->includes = $this->filterFilesByFiltersArray($this->configuration->getFileFilters()->getIncludes());

        $toHash = array_merge($this->types, $this->files, $this->resources, $this->includes);
        foreach ($toHash as $fullPath => $relativePath) {
            $hash = hash_file('sha256', $fullPath);
            if (is_string($hash))
                $this->hashes[$relativePath] = $hash;
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
        return $this->types;
    }

    /**
     * return moved project files from files section in configuration
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * return project resources files
     *
     * @return array
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * return project include files
     *
     * @return array
     */
    public function getIncludes(): array
    {
        return $this->includes;
    }

    /**
     * return filtered project files by FileFilter
     *
     * @param FileFilter $filter
     * @return array
     */
    public function filterFiles(FileFilter $filter): array
    {
        $projectFiles = array_flip($this->projectFiles);
        $files = [];

        if ($filter->hasExclude()) {
            $excludeArray = explode(';', $filter->getExclude());
            foreach ($excludeArray as $pattern) {
                foreach ($projectFiles as $key => $path)
                    if (fnmatch($pattern, $path, FNM_NOESCAPE))
                        unset($projectFiles[$key]);
            }
        }

        $include = $filter->getInclude();
        $offset = $filter->getOffset();
        $as = $filter->getAs();
        if (!is_null($as))
            $offset++;
        foreach ($projectFiles as $key => $path)
            if (fnmatch($include, $path, FNM_NOESCAPE))
                $files[$key] = preg_replace("/(.[^\/]+\/)/", '', $path, $offset);

        if (!is_null($as))
            foreach ($files as $key => $path)
                $files[$key] = $as . '/' . $path;

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

    public function getBuildContext(): BuildContext
    {
        $innerFiles = [];
        $outerFiles = [];
        $manifest = new Manifest($this->configuration);
        $manifest->setHashes($this->hashes);
        ContextBuilder::prepareTypedFiles($this, $manifest, $innerFiles);
        ContextBuilder::prepareMovedFiles($this, $manifest, $outerFiles);
        ContextBuilder::prepareResources($this, $manifest, $innerFiles);
        ContextBuilder::prepareIncludes($this, $manifest, $innerFiles);
        return new BuildContext($this, $this->configuration, $manifest, $innerFiles, $outerFiles);
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