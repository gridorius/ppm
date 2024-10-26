<?php

namespace Ppm\Packages\Storage;

use Ppm\Packages\Common\Storage\DependencyTreeBuilderBase;

class DependencyTreeBuilderRemote extends DependencyTreeBuilderBase
{
    protected array $dependencies = [];

    public function __construct(array $catalog)
    {
        $this->storage = new RemotePackageStorage($catalog['packages']);
        $this->dependencies = $catalog['depends'];
    }

    public function getDepends(string $name, string $version): ?array
    {
        return $this->dependencies[$name][$version];
    }
}