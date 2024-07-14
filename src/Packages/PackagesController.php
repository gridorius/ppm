<?php

namespace Packages;

use Assembly\Utils;
use Builder\Configuration\Contracts\IConfigurationCollection;
use Exception;
use Packages\Contracts\IPackageBuilder;
use Packages\Contracts\IRemoteManager;
use Packages\Contracts\ISources;

class PackagesController
{
    private UnpackLocalManager $localManager;
    private IRemoteManager $remoteManager;

    private ISources $sources;

    private IPackageBuilder $builder;

    public function __construct()
    {
        $tmpDirectory = TMP_DIRECTORY;
        $this->localManager = new UnpackLocalManager(Utils::path('packages'), $tmpDirectory);
        $this->sources = new Sources(Utils::path('sources.json'));
        $this->remoteManager = new RemoteManager($this->sources, $this->localManager, $tmpDirectory);
        $this->builder = new PackageBuilder($this->localManager);
    }

    public function addSource(string $source, ?string $alias = null): void
    {
        $this->sources->add(new Source($source), $alias);
    }

    public function deleteSource(string $source): void
    {
        $this->sources->delete($source);
    }

    public function getLocalManager(): UnpackLocalManager
    {
        return $this->localManager;
    }

    public function getRemoteManager(): IRemoteManager
    {
        return $this->remoteManager;
    }

    public function getSources(): ISources
    {
        return $this->sources;
    }

    public function getBuilder(): IPackageBuilder
    {
        return $this->builder;
    }

    public function unpackPackagesRecursive(IConfigurationCollection $configurationCollection, string $outDirectory): void
    {
        $packages = $configurationCollection->getDepends();
        $this->unpackPackages($packages, $outDirectory);
    }

    private function unpackPackages(array $packages, string $outDirectory): void
    {
        foreach ($packages as $name => $version) {
            if (is_null($localPackage = $this->localManager->get($name, $version)))
                throw new Exception("Package {$name}:{$version} not found in local registry");

            $localPackage->unpack($outDirectory);
            $this->unpackPackages($localPackage->getDepends(), $outDirectory);
        }
    }
}