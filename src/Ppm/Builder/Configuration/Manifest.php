<?php

namespace Ppm\Builder\Configuration;

class Manifest
{
    private array $resources = [];
    private array $types = [];
    private array $includes = [];
    private array $fileRelations = [];
    private array $hashes = [];
    private Configuration $configuration;
    private string $prefix;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->prefix = "phar://{$configuration->getProjectInfo()->getName()}/";
    }

    public function compareHashes(array $hashes): array
    {
        $changed = [];
        foreach ($hashes as $relativePath => $hash)
            if (!key_exists($relativePath, $this->hashes) || $this->hashes[$relativePath] !== $hash)
                $changed[] = $relativePath;

        return $changed;
    }

    public function getFileRelations(): array
    {
        return $this->fileRelations;
    }

    public function getRemovedFiles(array $hashes): array
    {
        $removed = [];
        foreach ($this->hashes as $relativePath => $hash)
            if (!key_exists($relativePath, $hashes))
                $removed[] = $relativePath;
        return $removed;
    }

    public function clearChanged(array $removed): static
    {
        foreach ($removed as $relativePath)
            if (key_exists($relativePath, $this->fileRelations)) {
                $data = $this->fileRelations[$relativePath];
                $key = $data[1];
                switch ($data[0]) {
                    case 'type':
                        unset($this->types[$key]);
                        break;
                    case 'resource':
                        unset($this->resources[$key]);
                        break;
                    case 'include':
                        unset($this->includes[$key]);
                        break;
                }
                unset($this->fileRelations[$relativePath]);
                unset($this->hashes[$relativePath]);
            }

        return $this;
    }

    public function mergeParent(Manifest $parent): Manifest
    {
        $this->resources = array_merge($parent->getResources(), $this->resources);
        $this->types = array_merge($parent->getTypes(), $this->types);
        $this->includes = array_merge($parent->getIncludes(), $this->includes);
        $this->hashes = array_merge($parent->getHashes(), $this->hashes);
        $this->fileRelations = array_merge($parent->getFileRelations(), $this->fileRelations);
        return $this;
    }

    public function setFileRelation(string $relativePath, string $type, ?string $key = null, ?string $innerPath = null): void
    {
        $this->fileRelations[$relativePath] = [$type, $key, $innerPath];
    }

    public function setHashes(array $hashes): void
    {
        foreach ($hashes as $key => $value)
            $this->hashes[$key] = $value;
    }

    public function setResources(array $resources): void
    {
        foreach ($resources as $key => $value)
            $this->resources[$key] = $value;
    }

    public function setTypes(array $types): void
    {
        foreach ($types as $key => $value)
            $this->types[$key] = $value;
    }

    public function setIncludes(array $includes): void
    {
        foreach ($includes as $key => $value)
            $this->includes[$key] = $value;
    }

    public function toArray(): array
    {
        $projectInfo = $this->configuration->getProjectInfo();
        return [
            'name' => $projectInfo->getName(),
            'version' => $projectInfo->getVersion(),
            'description' => $projectInfo->getDescription(),
            'author' => $projectInfo->getAuthor(),
            'resources' => array_map(function ($path) {
                return $this->getPathWithPrefix($path);
            }, $this->resources),
            'types' => array_map(function ($path) {
                return $this->getPathWithPrefix($path);
            }, $this->types),
            'includes' => array_map(function ($path) {
                return $this->getPathWithPrefix($path);
            }, $this->includes),
            'depends' => $this->configuration->getProjectDependencies()->getDependencies(),
            'commands' => $this->configuration->getCommands(),
            'hashes' => $this->hashes,
            'fileRelations' => $this->fileRelations,
            'meta' => $this->configuration->getMeta()
        ];
    }

    public function getPathWithPrefix(string $path): string
    {
        return $this->prefix . $path;
    }

    public function getFilesCount(): int
    {
        return count(array_filter($this->fileRelations, function ($relation) {
            return $relation[0] == 'file';
        }));
    }

    public function getResourcesCount(): int
    {
        return count($this->resources);
    }

    public function getTypesCount(): int
    {
        return count($this->types);
    }

    public function getIncludesCount(): int
    {
        return count($this->includes);
    }

    public function getDependsCount(): int
    {
        return count($this->configuration->getProjectDependencies()->getDependencies());
    }

    public function getResources(): array
    {
        return $this->resources;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function getIncludes(): array
    {
        return $this->includes;
    }

    public function getHashes(): array
    {
        return $this->hashes;
    }
}