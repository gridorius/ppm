<?php

namespace Builder\Configuration;

use Builder\Configuration\Contracts\IProjectConfiguration;
use Utils\PathUtils;

class ProjectConfiguration implements IProjectConfiguration
{
    private array $configuration;

    private string $directory;

    public function __construct(string $pathToProjectFile)
    {
        $this->configuration = PathUtils::getJson($pathToProjectFile);
        $this->directory = dirname($pathToProjectFile);
        $this->configuration['depends'] = [];
        if (empty($this->configuration['name']))
            $this->configuration['name'] = pathinfo(dirname($pathToProjectFile), PATHINFO_BASENAME);
    }

    public function getInclude(): string
    {
        return $this->configuration['include'] ?? '*.php';
    }

    public function getExclude(): ?string
    {
        return $this->configuration['exclude'] ?? null;
    }

    public function hasExclude(): bool
    {
        return !empty($this->configuration['exclude']);
    }

    public function getName(): string
    {
        return $this->configuration['name'];
    }

    public function getEntrypoint(): ?string
    {
        return $this->configuration['entrypoint'] ?? null;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function hasVersion(): bool
    {
        return !empty($this->configuration['version']);
    }

    public function hasEntrypoint(): bool
    {
        return !empty($this->configuration['entrypoint']);
    }

    public function hasFiles(): bool
    {
        return !empty($this->configuration['files']);
    }

    public function hasResources(): bool
    {
        return !empty($this->configuration['resources']);
    }

    public function hasIncludes(): bool
    {
        return !empty($this->configuration['includes']);
    }


    public function getVersion(): ?string
    {
        return $this->configuration['version'] ?? null;
    }

    public function setVersion(string $version): void
    {
        $this->configuration['version'] = $version;
    }

    public function getRunner(): ?string
    {
        return $this->configuration['runner'] ?? $this->getName();
    }

    public function getStub(): ?string
    {
        return $this->configuration['stub'];
    }

    public function getStubContent(): string
    {
        if (!$this->hasStub())
            throw new \Exception("Stub path not configured");

        return file_get_contents($this->directory . DIRECTORY_SEPARATOR . $this->getStub());
    }

    public function hasStub(): bool
    {
        return !empty($this->configuration['stub']);
    }

    public function getAuthor(): ?string
    {
        return $this->configuration['author'] ?? null;
    }

    public function getDescription(): ?string
    {
        return $this->configuration['description'] ?? null;
    }

    public function getProjectReferences(): array
    {
        return $this->configuration['projectReferences'] ?? [];
    }

    public function getPackageReferences(): array
    {
        return $this->configuration['packageReferences'] ?? [];
    }

    public function getIncludes(): array
    {
        return array_map(function ($filter) {
            return new ProjectFileFilter($filter);
        }, $this->configuration['includes'] ?? []);
    }

    public function getFiles(): array
    {
        return array_map(function ($filter) {
            return new ProjectFileFilter($filter);
        }, $this->configuration['files'] ?? []);
    }

    public function getResources(): array
    {
        return array_map(function ($filter) {
            return new ProjectFileFilter($filter);
        }, $this->configuration['resources'] ?? []);
    }

    public function addDepend(string $depend): void
    {
        $this->configuration['depends'][] = $depend;
    }

    public function getDepends(): array
    {
        return [...$this->configuration['depends'], ...array_keys($this->getPackageReferences())];
    }
}