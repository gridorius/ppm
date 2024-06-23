<?php

namespace PpmRegistry;

use PpmRegistry\Contracts\ILocalManager;
use PpmRegistry\Contracts\ILocalPackage;

class LocalManager implements ILocalManager
{
    protected string $packagesDirectory;
    protected array $localPackages;

    public function __construct(string $packagesDirectory)
    {
        $this->packagesDirectory = $packagesDirectory;
        if (!is_dir($packagesDirectory))
            mkdir($packagesDirectory, 0755, true);
        $this->scanPackages();
    }

    public function exist(string $name, string $concreteVersion): bool
    {
        return !empty($this->localPackages[$name][$concreteVersion]);
    }

    public function get(string $name, string $findVersion): ?ILocalPackage
    {
        if (!key_exists($name, $this->localPackages)) return null;
        if (is_null($version = $this->findLastVersion($name, $findVersion))) return null;
        return $this->localPackages[$name][$version];
    }

    public function findLocalPackage(string $name, string $version): string
    {
        $packages = glob($this->getLocalPath($name, $version));
        return end($packages);
    }

    public function getLocalPath(string $name, string $version): string
    {
        return $this->packagesDirectory . DIRECTORY_SEPARATOR . $this->getFileName($name, $version);
    }

    public function save(string $name, string $version, string $content): ILocalPackage
    {
        return $this->localPackages[$name][$version] =
            $this->createLocalPackage(
                $this->createPackageFile($name, $version, $content),
                $name,
                $version
            );
    }

    public function toArray(): array
    {
        return $this->localPackages;
    }

    protected function createPackageFile(string $name, string $version, string $content): string
    {
        $path = $this->packagesDirectory . DIRECTORY_SEPARATOR . $this->getFileName($name, $version);
        file_put_contents(
            $path,
            $content
        );
        return $path;
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

    private function scanPackages(): void
    {
        $this->localPackages = [];
        foreach (glob($this->packagesDirectory . DIRECTORY_SEPARATOR . '*.phar') as $packagePath) {
            [$name, $version] = explode('_', pathinfo($packagePath, PATHINFO_FILENAME));
            if (empty($this->localPackages[$name]))
                $this->localPackages[$name] = [];

            $this->localPackages[$name][$version] = $this->createLocalPackage($packagePath, $name, $version);
        }
    }
}