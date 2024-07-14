<?php

namespace Builder\Configuration\Contracts;

interface IManifestBuilder
{
    public function setName(string $name): void;

    public function setCommands(array $commands): void;

    public function setVersion(string $version): void;

    public function setDepends(array $depends): void;

    public function setTypes(array $types): void;

    public function setResources(array $resources): void;

    public function setIncludes(array $includes): void;

    public function buildForJson(): array;

    public function buildForPhp(): array;
}