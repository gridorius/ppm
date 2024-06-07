<?php

namespace Packages;

use Builder\Configuration\Contracts\IConfigurationCollection;
use Packages\Contracts\ILocalManager;
use Packages\Contracts\ILocalPackage;

class LocalManager implements ILocalManager
{
    protected string $packagesDirectory;

    public function __construct(string $packagesDirectory)
    {
        $this->packagesDirectory = $packagesDirectory;
        if (!is_dir($packagesDirectory))
            mkdir($packagesDirectory, 0755, true);
    }

    public function exist(string $name, string $version): bool
    {
        $packages = glob($this->getLocalPath($name, $version));
        return !empty($packages);
    }

    public function get(string $name, string $version): ILocalPackage
    {
        return new LocalPackage($this->findLocalPackage($name, $version));
    }

    public function getFileName(string $name, string $version): string
    {
        return $name . '_' . $version . '.phar';
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

    public function save(string $name, string $version, string $content): void
    {
        file_put_contents(
            $this->packagesDirectory . DIRECTORY_SEPARATOR . $this->getFileName($name, $version),
            $content
        );
    }

    public function unpackPackagesRecursive(IConfigurationCollection $configurationCollection, string $outDirectory): void
    {
        $packages = $configurationCollection->getPackages();
        $this->unpackPackages($packages, $outDirectory);
    }

    private function unpackPackages(array $packages, string $outDirectory)
    {
        foreach ($packages as $name => $version) {
            if (!$this->exist($name, $version))
                throw new \Exception("Package {$name}:{$version} not found in local registry");

            $localPackage = $this->get($name, $version);
            $localPackage->unpack($outDirectory);
            $this->unpackPackages($localPackage->getDepends(), $outDirectory);
        }
    }
}