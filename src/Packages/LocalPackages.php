<?php

namespace Packages;

use Utils\PathUtils;

class LocalPackages
{
    protected string $packagesPath;

    public function __construct(string $packagesPath)
    {
        $this->packagesPath = $packagesPath;
    }

    public function findPackage(string $name, string $version): ?string
    {
        $packages = glob($this->packagesPath . DIRECTORY_SEPARATOR . PathUtils::getPackageName($name, $version));
        if (empty($packages))
            return null;
        return end($packages);
    }
}