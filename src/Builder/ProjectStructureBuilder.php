<?php

namespace Builder;

use Utils\PathUtils;

class ProjectStructureBuilder
{
    protected string $projectDir;
    protected int $prefixLength;
    protected array $conf;
    protected array $files;
    protected array $depends = [];

    public function __construct(string $projectDir, array $configuration, array $files, array $depends = [])
    {
        $this->projectDir = $projectDir;
        $this->prefixLength = strlen($projectDir) + 1;
        $this->conf = $configuration;
        $this->files = $files;
        $this->depends = $depends;
    }

    public function build(): ProjectStructure
    {
        $projectStructure = new ProjectStructure($this->conf['name'], $this->conf['version'], $this->depends);
        $this->fillStructureFromConfiguration($projectStructure);
        $this->buildTypeFiles($projectStructure);
        $this->buildMoveFiles($projectStructure);
        $this->buildResources($projectStructure);
        $this->buildIncludes($projectStructure);
        return $projectStructure;
    }

    public function buildTypeFiles(ProjectStructure $structure): void
    {
        $includeTemplate = $this->conf['include'] ?? '*.php';
        $exclude = $this->conf['exclude'] ?? null;
        $files = $this->filterFiles(PathUtils::resolveRelativePath($this->projectDir, $includeTemplate), $exclude);
        foreach ($files as $path) {
            $types = EntityFinder::findByTokens($path);
            foreach ($types as $type) {
                $localPath = 'lib/' . $this->prepareLocalPath($path);
                $structure->manifest['types'][$type] = $localPath;
                $structure->innerMove[$localPath] = $path;
            }
        }
    }

    public function buildMoveFiles(ProjectStructure $structure): void
    {
        if (empty($this->conf['files'])) return;
        $files = $this->getFilesByFilterArray($this->conf['files']);
        $this->makeAsOuter($files, $structure);
    }

    public function buildResources(ProjectStructure $structure): void
    {
        if (empty($this->conf['resources'])) return;
        $resourceFiles = $this->getFilesByFilterArray($this->conf['resources']);
        $structure->manifest['resources'] = $this->makeAsInner($resourceFiles, 'resources', $structure);
    }

    public function buildIncludes(ProjectStructure $structure): void
    {
        if (empty($this->conf['includes'])) return;
        $includeFiles = $this->getFilesByFilterArray($this->conf['includes']);
        $structure->manifest['includes'] = $this->makeAsInner($includeFiles, 'includes', $structure);
    }

    protected function fillStructureFromConfiguration(ProjectStructure $structure)
    {
        $structure->entrypoint = $this->conf['entrypoint'] ?? null;
        $structure->runner = $this->conf['runner'] ?? $this->conf['name'];
        $structure->manifest['author'] = $this->conf['author'] ?? null;
        $structure->manifest['description'] = $this->conf['description'] ?? null;
    }

    protected function prepareLocalPath(string $path): string
    {
        return substr($path, $this->prefixLength);
    }

    protected function makeAsInner(array $files, string $prefix, ProjectStructure $structure): array
    {
        $innerFiles = [];
        foreach ($files as $path) {
            $localPath = $this->prepareLocalPath($path);
            $localPath = $innerFiles[$localPath] = $prefix . DIRECTORY_SEPARATOR . $localPath;
            $structure->innerMove[$localPath] = $path;
        }

        return $innerFiles;
    }

    protected function makeAsOuter(array $files, ProjectStructure $structure): void
    {
        foreach ($files as $path) {
            $structure->outerMove[$this->prepareLocalPath($path)] = $path;
        }
    }

    protected function getFilesByFilterArray(array $filters): array
    {
        $files = [];
        foreach ($filters as $fileFilter) {
            foreach ($this->filterFiles($fileFilter['include'], $fileFilter['exclude'] ?? null) as $file)
                $files[] = $file;
        }
        return $files;
    }

    protected function filterFiles(string $include, string $exclude = null): array
    {
        $projectFiles = $this->files;
        $files = [];
        $include = PathUtils::resolveRelativePath($this->projectDir, $include);
        $exclude = empty($exclude) ? [] : array_map(function ($template) {
            return PathUtils::resolveRelativePath($this->projectDir, $template);
        }, explode(';', $exclude));

        foreach ($exclude as $t) {
            foreach ($projectFiles as $key => $path) {
                if (fnmatch($t, $path, FNM_NOESCAPE))
                    unset($projectFiles[$key]);
            }
        }

        foreach ($projectFiles as $path) {
            if (fnmatch($include, $path, FNM_NOESCAPE))
                $files[] = $path;
        }

        return $files;
    }
}