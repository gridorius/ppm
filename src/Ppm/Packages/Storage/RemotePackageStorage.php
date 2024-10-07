<?php

namespace Ppm\Packages\Storage;

class RemotePackageStorage extends PackageStorageBase
{
    public function __construct(array $packages)
    {
        $this->packages = $packages;
    }

    public function findLastVersion(string $name, string $findVersion): ?string
    {
        if (empty($this->packages[$name]))
            return null;

        $found = array_filter($this->packages[$name], function ($version) use ($findVersion) {
            return fnmatch($findVersion, $version);
        });

        return empty($found) ? null : max($found);
    }
}