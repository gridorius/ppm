<?php

namespace Packages;

use Assembly\Utils;
use Packages\Contracts\ILocalManager;
use Packages\Contracts\IPackageBuilder;
use Packages\Contracts\IRemoteManager;
use Packages\Contracts\ISources;

class PackagesController
{
    private ILocalManager $localManager;
    private IRemoteManager $remoteManager;

    private ISources $sources;

    private IPackageBuilder $builder;

    public function __construct()
    {
        $this->localManager = new LocalManager(Utils::path('packages'));
        $this->sources = new Sources(Utils::path('sources.json'));
        $this->remoteManager = new RemoteManager($this->sources, $this->localManager);
        $this->builder = new PackageBuilder($this->localManager, Utils::path('tmp'));
    }

    public function addSource(string $source)
    {
        $this->sources->add(new Source($source));
    }

    public function deleteSource(string $source)
    {
        $this->sources->delete(new Source($source));
    }

    public function getLocalManager(): ILocalManager
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
}