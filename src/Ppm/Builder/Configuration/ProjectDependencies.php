<?php

namespace Ppm\Builder\Configuration;

class ProjectDependencies
{
    private array $projects;
    private array $packages;
    private array $dependencies;

    public function __construct(array $projects, array $packages)
    {
        $this->projects = $projects;
        $this->packages = $packages;
        $this->dependencies = array_keys($this->packages);
    }

    public function getProjects(): array
    {
        return $this->projects;
    }

    public function getPackages(): array
    {
        return $this->packages;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function addDepend(string $name): void
    {
        $this->dependencies[] = $name;
    }
}