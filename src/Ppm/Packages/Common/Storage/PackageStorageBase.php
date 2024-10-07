<?php

namespace Ppm\Packages\Common\Storage;

abstract class PackageStorageBase
{
    protected array $packages = [];

    abstract public function findLastVersion(string $name, string $findVersion): ?string;

    public function getPackages(): array
    {
        return $this->packages;
    }
}