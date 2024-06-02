<?php

namespace Builder;

class ProjectStructure
{
    public array $manifest;
    public array $innerMove = [];
    public array $outerMove = [];
    public ?string $entrypoint;

    public array $files = [];

    public ?string $runner;

    public function __construct(string $name, string $version, array $depends)
    {
        $this->manifest = [
            'name' => $name,
            'version' => $version,
            'depends' => $depends,
            'types' => [],
            'resources' => [],
            'includes' => []
        ];
    }
}