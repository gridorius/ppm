<?php

namespace Packages;

use Packages\Contracts\ILocalManager;
use Packages\Contracts\ILocalPackage;
use Utils\PathUtils;

class LocalManager implements ILocalManager
{
    protected string $packagesDirectory;
    protected array $localPackages;
    private string $tmpDirectory;

    public function __construct(string $packagesDirectory, string $tmpDirectory)
    {
        $this->packagesDirectory = PathUtils::createDirectory($packagesDirectory);
        $this->tmpDirectory = PathUtils::createDirectory($tmpDirectory);
        $this->scanPackages();
    }

    public function exist(string $name, string $concreteVersion): bool
    {
        return !empty($this->localPackages[$name][$concreteVersion]);
    }

    public function get(string $name, string $findVersion): ?ILocalPackage
    {
        if (!key_exists($name, $this->localPackages) || is_null($version = $this->findLastVersion($name, $findVersion)))
            return null;
        return $this->localPackages[$name][$version];
    }

    public function findLocalPackage(string $name, string $version): string
    {
        $packages = glob($this->getLocalPath($name, $version));
        return end($packages);
    }

    public function getLocalPath(string $name, string $version): string
    {
        return $this->packagesDirectory . DIRECTORY_SEPARATOR . $this->getDirectoryName($name, $version);
    }

    public function save(string $name, string $version, string $content): ILocalPackage
    {
        return $this->localPackages[$name][$version] =
            $this->createLocalPackage(
                $this->createPackageFromContent($name, $version, $content),
                $name,
                $version
            );
    }

    public function scanPackageVersions(string $packageName): void
    {
        foreach (glob($this->packagesDirectory . DIRECTORY_SEPARATOR . $packageName . '*.phar') as $packagePath) {
            [$name, $version] = explode('_', pathinfo($packagePath, PATHINFO_FILENAME));
            if (empty($this->localPackages[$name]))
                $this->localPackages[$name] = [];
            if (empty($this->localPackages[$name][$version]))
                $this->localPackages[$name][$version] = $this->createLocalPackage($packagePath, $name, $version);
        }
    }

    public function toArray(): array
    {
        return $this->localPackages;
    }

    protected function createPackageFromContent(string $name, string $version, string $content): string
    {
        $tmpPackagePhar = $this->tmpDirectory . DIRECTORY_SEPARATOR . $this->getFileName($name, $version);
        $packageDirectory = PathUtils::createDirectory(
            $this->packagesDirectory . DIRECTORY_SEPARATOR . $this->getDirectoryName($name, $version));
        file_put_contents(
            $tmpPackagePhar,
            $content
        );
        $phar = new \Phar($tmpPackagePhar);
        $phar->extractTo($packageDirectory, null, true);
        Metadata::createMetadataFile($packageDirectory, $phar->getMetadata());
        return $packageDirectory;
    }

    protected function findLastVersion(string $name, string $findVersion): ?string
    {
        $foundVersion = null;
        foreach ($this->localPackages[$name] as $version => $package)
            if (fnmatch($findVersion, $version))
                $foundVersion = $version;

        return $foundVersion;
    }

    protected function createLocalPackage(string $path, string $name, string $version): ILocalPackage
    {
        return new LocalPackage($path, $name, $version);
    }

    protected function getFileName(string $name, string $version): string
    {
        return $name . '_' . $version . '.phar';
    }

    protected function getDirectoryName(string $name, string $version): string
    {
        return $name . '_' . $version;
    }

    private function scanPackages(): void
    {
        $this->localPackages = [];
        foreach (glob($this->packagesDirectory . DIRECTORY_SEPARATOR . '*') as $packagePath) {
            [$name, $version] = explode('_', pathinfo($packagePath, PATHINFO_BASENAME));
            if (empty($this->localPackages[$name]))
                $this->localPackages[$name] = [];

            $this->localPackages[$name][$version] = $this->createLocalPackage($packagePath, $name, $version);
        }
    }
}