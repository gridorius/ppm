<?php

namespace Ppm\Packages\Common\Storage;

class DependencyTreeBuilderLocal extends DependencyTreeBuilderBase
{
    protected array $dependencies = [];

    public function __construct(PackagesStorage $storage)
    {
        $this->storage = $storage;
        $this->rebuild();
    }

    public function rebuild(): void
    {
        $packages = $this->storage->getPackages();
        foreach ($packages as $name => $versions) {
            foreach ($versions as $version => $package) {
                /**
                 * @var Package $package
                 */
                $this->dependencies[$name][$version] = $package->getDepends();
            }
        }
    }

    protected function getDepends(string $name, string $version): ?array
    {
        return $this->dependencies[$name][$version] ?? null;
    }
}