<?php

namespace Builder;

use Builder\Configuration\Contracts\IConfigurationFileFilter;
use Builder\Configuration\Contracts\IProjectConfiguration;
use Builder\Contracts\IProjectInfo;

class ProjectInfo implements IProjectInfo
{
    private array $files;
    private IProjectConfiguration $configuration;

    /**
     * @param array $files
     * @param IProjectConfiguration $configuration
     */
    public function __construct(array $files, IProjectConfiguration $configuration)
    {
        $this->files = $files;
        $this->configuration = $configuration;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getConfiguration(): IProjectConfiguration
    {
        return $this->configuration;
    }

    public function filterFiles(IConfigurationFileFilter $filter): array
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

    public function filterByArray(array $filters): array
    {
        $files = [];
        foreach ($filters as $filter)
            foreach ($this->filterFiles($filter) as $realPath => $relativePath)
                $files[$realPath] = $relativePath;

        return $files;
    }
}