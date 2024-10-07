<?php

namespace Ppm\Packages\Storage;

use Ppm\Framework\Filesystem\Directory;
use Ppm\Packages\MetadataUtil;
use Ppm\Packages\PackageUtils;

class PackagesStorage extends PackageStorageBase
{
    protected Directory $directory;
    protected DependencyTreeBuilderLocal $dependencyTreeBuilder;

    public function __construct(string $path)
    {
        $this->directory = (new Directory($path))->create();
        $this->dependencyTreeBuilder = new DependencyTreeBuilderLocal($this);
        $this->scan();
    }

    public function scan(): void
    {
        $this->packages = [];
        foreach ($this->directory->glob(DIRECTORY_SEPARATOR . '*') as $packagePath) {
            [$name, $version] = PackageUtils::parsePackageName($packagePath);
            $this->registerPackage($name, $version, $packagePath);
        }
    }

    public function registerPackage(string $name, $version, string $path): void
    {
        $this->packages[$name][$version] = new Package($path);
    }

    public function get(string $name, string $version): ?Package
    {
        return $this->packages[$name][$version];
    }

    public function find(string $name, string $version): ?Package
    {
        $version = $this->findLastVersion($name, $version);
        if (is_null($version))
            return null;

        return $this->packages[$name][$version];
    }

    public function findLastVersion(string $name, string $findVersion): ?string
    {
        if (!key_exists($name, $this->packages)) return null;
        $versions = array_filter(array_keys($this->packages[$name]), function ($version) use ($findVersion) {
            return fnmatch($findVersion, $version);
        });

        if (empty($versions))
            return null;

        return max($versions);
    }

    public function import(string $path): void
    {
        $metadata = MetadataUtil::getPackageMetadata($path);
        $this
            ->directory
            ->copyFileFrom($path, PackageUtils::makePackagePharName($metadata->getName(), $metadata->getVersion()));
    }

    public function getDependencyTreeBuilder(): DependencyTreeBuilderLocal
    {
        return $this->dependencyTreeBuilder;
    }

    public function extractPackages(array $packages, Directory $toDirectory): void
    {
        foreach ($packages as $name => $version)
            $this->get($name, $version)->extractTo($toDirectory);
    }
}