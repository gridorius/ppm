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
    private string $name;
    private ?string $author;
    private ?string $description;
    private ?string $runner;
    /**
     * @var Configuration[]
     */
    private array $projects;
    private array $packages;
    private ?string $stub;
    private ?string $entrypoint;
    private string $version;
    /** @var FileFilter[] */
    private array $files;
    /** @var FileFilter[] */
    private array $resources;
    /** @var FileFilter[] */
    private array $includes;
    private array $depends;
    private array $commands;
    private string $type;

    public function __construct(string $pathToProjectFile)
    {
        static::$projectsCache[$pathToProjectFile] = $this;
        $configuration = PathUtils::parseJson($pathToProjectFile, true);
        $this->directory = dirname($pathToProjectFile);
        $this->actions = new Actions($configuration['actions'] ?? []);
        $this->name = $configuration['name'] ?? pathinfo($this->directory, PATHINFO_BASENAME);
        $this->author = $configuration['author'] ?? null;
        $this->description = $configuration['description'] ?? null;
        $this->runner = $configuration['runner'] ?? $this->name;
        $this->packages = $configuration['packages'] ?? [];
        $this->depends = array_keys($this->packages);
        $this->commands = $configuration['commands'] ?? [];
        $this->makeSubConfigurations($configuration['projects'] ?? []);
        $this->stub = $configuration['stub'] ?? null;
        $this->type = $configuration['type'] ?? 'library';
        $this->entrypoint = $configuration['entrypoint'] ?? null;
        $this->version = $configuration['version'] ?? 'latest';
        $this->files = array_map(function ($conf) {
            return new FileFilter($conf);
        }, $configuration['files'] ?? []);
        $this->resources = array_map(function ($conf) {
            return new FileFilter($conf);
        }, $configuration['resources'] ?? []);
        $this->includes = array_map(function ($conf) {
            return new FileFilter($conf);
        }, $configuration['includes'] ?? []);

        $configuration['include'] = $configuration['include'] ?? '*.php';
        $exclude = empty($exclude) ? [] : explode(';', $configuration['exclude'] ?? '');
        foreach ($this->getResources() as $resource) {
            $exclude[] = $resource->getInclude();
        }
        $configuration['exclude'] = implode(';', $exclude);
        parent::__construct($configuration);
    }

    public function getDepends(): array
    {
        return $this->depends;
    }

    public function hasVersion(): bool
    {
        return !empty($this->version);
    }

    public function hasEntrypoint(): bool
    {
        return !empty($this->entrypoint);
    }

    public function hasFiles(): bool
    {
        return !empty($this->files);
    }

    public function hasResources(): bool
    {
        return !empty($this->resources);
    }

    public function hasIncludes(): bool
    {
        return !empty($this->includes);
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getActions(): Actions
    {
        return $this->actions;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getRunner(): ?string
    {
        return $this->runner;
    }

    public function getProjects(): array
    {
        return $this->projects;
    }

    public function getPackages(): array
    {
        return $this->packages;
    }

    public function getStub(): ?string
    {
        return $this->stub;
    }

    public function getEntrypoint(): ?string
    {
        return $this->entrypoint;
    }

    public function getVersion(): ?string
    {
        return $this->version;
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
            $this->depends[] = $configuration->getName();
        }
    }

    public function getType(): string
    {
        return $this->type;
    }
}