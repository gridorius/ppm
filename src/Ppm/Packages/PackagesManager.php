<?php

namespace Ppm\Packages;

use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Filesystem\TmpManager;
use Ppm\Packages\Common\Storage\PackagesStorage;
use Ppm\Packages\Services\RestoreService;
use Ppm\Packages\Sources\Source;
use Ppm\Packages\Sources\Sources;

class PackagesManager
{
    private PackagesStorage $storage;
    private RemoteManager $remoteManager;
    private PackageBuilder $builder;
    private Sources $sources;
    private RestoreService $restoreService;

    private Directory $extensions;

    public function __construct()
    {
        $this->extensions = Directory::from(Path::assemblyCombine('extensions'))->create();
        $tmp = (new TmpManager(TMP_DIRECTORY))->create();
        $this->sources = new Sources(Path::assemblyCombine('sources.json'));
        $this->remoteManager = new RemoteManager($this->sources, $tmp, Directory::createDirectory(Path::assemblyCombine('catalog')));
        $this->storage = new PackagesStorage(
            Directory::createDirectory(Path::assemblyCombine('packages')),
        );
        $this->builder = new PackageBuilder($this->storage, $tmp);
        $this->restoreService = new RestoreService($this->storage, $this->remoteManager, $tmp);
    }

    public function getExtensionsDirectory(): Directory
    {
        return $this->extensions;
    }

    public function getBuilder(): PackageBuilder
    {
        return $this->builder;
    }

    public function addSource(string $source, ?string $alias = null): void
    {
        $this->sources->add(new Source($source), $alias);
    }

    public function deleteSource(string $source): void
    {
        $this->sources->delete($source);
    }

    public function getStorage(): PackagesStorage
    {
        return $this->storage;
    }

    public function getRemoteManager(): RemoteManager
    {
        return $this->remoteManager;
    }

    public function getSources(): Sources
    {
        return $this->sources;
    }

    public function getRestoreService(): RestoreService
    {
        return $this->restoreService;
    }
}