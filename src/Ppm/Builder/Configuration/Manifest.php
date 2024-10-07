<?php

namespace Ppm\Builder\Configuration;

class Manifest
{
    private array $resources;
    private array $types;
    private array $includes;
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function setResources(array $resources): void
    {
        $this->resources = $resources;
    }

    public function setTypes(array $types): void
    {
        $this->types = $types;
    }

    public function setIncludes(array $includes): void
    {
        $this->includes = $includes;
    }

    public function toArray(): array
    {
        $prefix = "phar://{$this->configuration->getName()}/";
        return [
            'name' => $this->configuration->getName(),
            'version' => $this->configuration->getVersion(),
            'description' => $this->configuration->getDescription(),
            'author' => $this->configuration->getAuthor(),
            'resources' => array_map(function ($path) use ($prefix) {
                return $prefix . $path;
            }, $this->resources),
            'types' => array_map(function ($path) use ($prefix) {
                return $prefix . $path;
            }, $this->types),
            'includes' => array_map(function ($path) use ($prefix) {
                return $prefix . $path;
            }, $this->includes),
            'depends' => $this->configuration->getDepends(),
            'commands' => $this->configuration->getCommands()
        ];
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
        return count($this->configuration->getDepends());
    }
}