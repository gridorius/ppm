<?php

namespace Ppm\Packages\Storage;

class DependencyTreeBuilderRemote extends DependencyTreeBuilderBase
{
    protected array $dependencies = [];

    public function __construct(array $catalog)
    {
        $this->storage = new RemotePackageStorage($catalog['packages']);
        $this->dependencies = $catalog['depends'];
    }

    protected function getDepends(string $name, string $version): ?array
    {
        return $this->dependencies[$name][$version];
    }
}