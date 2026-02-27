<?php

namespace Ppm\Packages\Common\Storage;

use Phar;
use PharData;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\File;
use Ppm\Framework\Filesystem\TmpManager;
use Ppm\Packages\Common\MetadataUtil;
use Ppm\Packages\Common\PackageUtils;

class PackagesStorage extends PackageStorageBase
{
    protected Directory $directory;
    protected DependencyTreeBuilderLocal $dependencyTreeBuilder;
    protected TmpManager $tmp;

    public function __construct(string $path, TmpManager $tmp)
    {
        $this->directory = (new Directory($path))->create();
        $this->tmp = $tmp;
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
        $name = PackageUtils::makePackagePharName($metadata->getName(), $metadata->getVersion());
        $this
            ->directory
            ->copyFileFrom($path, $name);
        $this->registerPackage($metadata->getName(), $metadata->getVersion(), $this->directory->getFile($name)->getPath());
    }

    public function export(array $packages): ?File
    {
        $found = [];
        foreach ($packages as $packageName => $packageVersions) {
            $package = $this->packages[$packageName][$packageVersions];
            if (!empty($package))
                $found[] = $package;
        }

        if (empty($found))
            return null;

        $file = $this->tmp->getTmpFile('tar');
        $phar = new PharData($file->getPath());
        $phar->startBuffering();
        foreach ($found as $package)
            $phar->addFile($package->getPath(), $package->getPharName());
        $phar->stopBuffering();
        return $file;
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