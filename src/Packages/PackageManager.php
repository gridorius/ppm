<?php

namespace Packages;

use Builder\RecursiveConfigurationScanner;
use Exception;
use Phar;
use Utils\PathUtils;

class PackageManager
{
    protected string $packagesPath;
    protected PackageBuilder $builder;
    protected LocalPackages $local;
    protected RemotePackages $remote;

    public function __construct()
    {
        $this->packagesPath = \Assembly::path('packages');
        $this->builder = new PackageBuilder($this->packagesPath, \Assembly::path('tmp'));
        $this->local = new LocalPackages($this->packagesPath);
        $this->remote = new RemotePackages();
    }

    public function getRemote(): RemotePackages{
        return $this->remote;
    }

    public function getLocal(): LocalPackages{
        return $this->local;
    }

    public function getBuilder(): PackageBuilder{
        return $this->builder;
    }

    public function restore(string $pathToProj)
    {
        $scanner = new RecursiveConfigurationScanner($pathToProj);
        $scanner->scan();
        $packages = $scanner->getPackages();
        $this->restorePackages($packages);
    }

    public function getPackagesRecursive(array $packages, array &$result = []): array{
        foreach ($packages as $package => $version){
            $path = $this->local->findPackage($package, $version);
            if(is_null($path))
                throw new Exception("Package {$package}:{$version} not found in local repository");

            $metadata = $this->getPackageMetadata($path);
            $this->getPackagesRecursive($metadata['depends'], $result);
            $result[$path] = $path;
        }

        return $result;
    }

    public function unpackPackages(array $packages, string $outDirectory){
        foreach ($packages as $path){
            $phar = new Phar($path);
            $phar->extractTo($outDirectory);
        }
    }

    public function restorePackages(array $packages)
    {
        foreach ($packages as $package => $version) {
            $localPackage = $this->local->findPackage($package, $version);
            if (is_null($localPackage)) {
                echo "{$package}:{$version} not found in local repository\n";
                $packageInfo = $this->remote->findPackage($package, $version);
                if (is_null($packageInfo))
                    throw new Exception("Package {$package}:{$version} not found");

                $localPath = $this->remote->downloadPackage(
                    $package,
                    $packageInfo['package']['version'],
                    $packageInfo['source'],
                    $this->packagesPath
                );

                $metadata = $this->getPackageMetadata($localPath);
                $this->restorePackages($metadata['depends']);
            }else{
                $metadata = $this->getPackageMetadata($localPackage);
                $this->restorePackages($metadata['depends']);
            }
        }
    }

    protected function getPackageMetadata(string $packagePath){
        $phar = new Phar($packagePath);
        return $phar->getMetadata();
    }
}