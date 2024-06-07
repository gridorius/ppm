<?php

namespace Builder\Configuration;

use Builder\Configuration\Contracts\IManifestBuilder;
use Builder\Configuration\Contracts\IManifestInformation;

class ManifestBuilder implements IManifestBuilder, IManifestInformation
{
    private array $manifest;

    public function __construct()
    {
        $this->manifest = [
            'types' => [],
            'resources' => [],
            'includes' => [],
        ];
    }

    public function setName(string $name): void
    {
        $this->manifest['name'] = $name;
    }

    public function setVersion(string $version): void
    {
        $this->manifest['version'] = $version;
    }

    public function setDepends(array $depends): void
    {
        $this->manifest['depends'] = $depends;
    }

    public function setTypes(array $types): void
    {
        $this->manifest['types'] = $types;
    }

    public function setResources(array $resources): void
    {
        $this->manifest['resources'] = $resources;
    }

    public function setIncludes(array $includes): void
    {
        $this->manifest['includes'] = $includes;
    }

    public function build(): array
    {
        return $this->manifest;
    }

    public function getTypeCount(): int
    {
        return count($this->manifest['types']);
    }

    public function getResourcesCount(): int
    {
        return count($this->manifest['resources']);
    }

    public function getIncludesCount(): int
    {
        return count($this->manifest['includes']);
    }

    public function getDependsCount(): int
    {
        return count($this->manifest['depends']);
    }
}