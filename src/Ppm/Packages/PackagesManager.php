<?php

namespace Ppm\Packages;

use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Filesystem\TmpManager;
use Ppm\Packages\Services\RestoreService;
use Ppm\Packages\Sources\Source;
use Ppm\Packages\Sources\Sources;
use Ppm\Packages\Storage\PackagesStorage;

class PackagesManager
{
    private PackagesStorage $storage;
    private RemoteManager $remoteManager;
    private PackageBuilder $builder;
    private Sources $sources;

    private RestoreService $restoreService;

    public function __construct()
    {
        $ppmDirectory = WIN ? Path::assemblyCombine() : (posix_getpwuid(posix_getuid())['dir'] . '/.ppm');
        Directory::createDirectory($ppmDirectory);
        $tmp = new TmpManager(TMP_DIRECTORY);
        $this->sources = new Sources(Path::combine($ppmDirectory, 'sources.json'));
        $this->remoteManager = new RemoteManager($this->sources, $tmp, Directory::createDirectory($ppmDirectory . DIRECTORY_SEPARATOR . 'catalog'));
        $this->storage = new PackagesStorage(
            Directory::createDirectory($ppmDirectory . DIRECTORY_SEPARATOR . 'packages'),
        );
        $this->builder = new PackageBuilder($this->storage, $tmp);
        $this->restoreService = new RestoreService($this->storage, $this->remoteManager, $tmp);
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