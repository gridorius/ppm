<?php

namespace Packages;

use Packages\Contracts\IUnpack;

class LocalManager extends \PpmRegistry\LocalManager
{
    protected function createLocalPackage(string $path, string $name, string $version): IUnpack
    {
        return new LocalPackage($path, $name, $version);
    }

    public function get(string $name, string $findVersion): ?IUnpack
    {
        if (!key_exists($name, $this->localPackages) || is_null($version = $this->findLastVersion($name, $findVersion)))
            return null;
        return $this->localPackages[$name][$version];
    }
}