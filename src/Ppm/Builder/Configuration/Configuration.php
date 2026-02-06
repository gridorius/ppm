<?php

namespace Ppm\Builder\Configuration;

use Exception;
use Ppm\Builder\ProjectFile;
use Ppm\Framework\Filesystem\PathUtils;

class Configuration extends FileFilter
{
    private static array $projectsCache = [];
    private string $directory;
    private Actions $actions;
    private ?string $runner;
    /**
     * @var Configuration[]
     */
    private array $projects;
    private ?string $stub;
    private ?string $entrypoint;
    private array $commands;
    private ProjectInfo $projectInfo;
    private ProjectDependencies $projectDependencies;
    private FileFilters $fileFilters;
    private array $meta = [];

    public function __construct(string $pathToProjectFile)
    {
        static::$projectsCache[$pathToProjectFile] = $this;
        $this->directory = dirname($pathToProjectFile);
        $configuration = PathUtils::parseJson($pathToProjectFile, true);
        $name = $configuration['name'] ?? pathinfo($this->directory, PATHINFO_BASENAME);
        $this->projectInfo = new ProjectInfo(
            $name,
            $configuration['version'] ?? 'latest',
            $configuration['author'] ?? '',
            $configuration['description'] ?? ''
        );
        $this->projectDependencies = new ProjectDependencies($configuration['projects'] ?? [], $configuration['packages'] ?? []);
        $this->fileFilters = new FileFilters(
            $configuration['files'] ?? [],
            $configuration['resources'] ?? [],
            $configuration['includes'] ?? []
        );

        $this->actions = new Actions($configuration['actions'] ?? []);
        $this->runner = $configuration['runner'] ?? $this->projectInfo->getName();
        $this->commands = $configuration['commands'] ?? [];
        $this->makeSubConfigurations($configuration['projects'] ?? []);
        $this->stub = $configuration['stub'] ?? null;
        $this->entrypoint = $configuration['entrypoint'] ?? null;
        $this->meta = $configuration['meta'] ?? [];
        parent::__construct([
            'include' => $configuration['include'] ?? '*.php',
            'exclude' => empty($configuration['exclude']) ? null : $this->prepareExclude($configuration['exclude']),
        ]);
    }

    public function getProjectDependencies(): ProjectDependencies
    {
        return $this->projectDependencies;
    }

    public function getProjectInfo(): ProjectInfo
    {
        return $this->projectInfo;
    }

    public function getFileFilters(): FileFilters
    {
        return $this->fileFilters;
    }

    public function hasEntrypoint(): bool
    {
        return !empty($this->entrypoint);
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getActions(): Actions
    {
        return $this->actions;
    }

    public function getRunner(): ?string
    {
        return $this->runner;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getEntrypoint(): ?string
    {
        return $this->entrypoint;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getStubPath(): string
    {
        if (!$this->hasStub())
            throw new Exception("Stub path not configured");

        return $this->directory . DIRECTORY_SEPARATOR . $this->stub;
    }

    public function hasStub(): bool
    {
        return !empty($this->stub);
    }

    public function buildConfigurationCollection(): ConfigurationCollection
    {
        $collection = new ConfigurationCollection([
            $this->getDirectory() => $this
        ]);
        foreach ($this->projects as $project)
            $collection->mergeCollection($project->buildConfigurationCollection());

        return $collection;
    }

    private function makeSubConfigurations(array $projects): void
    {
        $this->projects = [];
        foreach ($projects as $relativePath) {
            $fullPath = PathUtils::resolveRelativePath($this->directory, $relativePath);
            $projectFile = ProjectFile::getPathOrThrow($fullPath);

            if (!empty(static::$projectsCache[$projectFile]))
                $configuration = $this->projects[] = static::$projectsCache[$projectFile];
            else {
                $configuration = $this->projects[] = new Configuration($projectFile);
                static::$projectsCache[$projectFile] = $configuration;
            }
            $this->projectDependencies->addDepend($configuration->getProjectInfo()->getName());
        }
    }

    private function prepareExclude(string $exclude): string
    {
        $exclude = empty($exclude) ? [] : explode(';', $configuration['exclude'] ?? '');
        foreach ($this->getFileFilters()->getResources() as $resource)
            $exclude[] = $resource->getInclude();

        return implode(';', $exclude);
    }
}