<?php

namespace Ppm\Packages\Services;

use PharData;
use Ppm\Framework\Exception;
use Ppm\Framework\Filesystem\File;
use Ppm\Framework\Filesystem\TmpManager;
use Ppm\Packages\RemoteManager;
use Ppm\Packages\Storage\PackagesStorage;

class RestoreService
{
    private PackagesStorage $storage;
    private RemoteManager $remoteManager;
    private TmpManager $tmp;

    public function __construct(PackagesStorage $storage, RemoteManager $remoteManager, TmpManager $tmp)
    {
        $this->storage = $storage;
        $this->remoteManager = $remoteManager;
        $this->tmp = $tmp;
    }

    public function restore(array $packages): void
    {
        $localResult = $this->storage->getDependencyTreeBuilder()->buildPackagesTree($packages);
        if (!$localResult->hasNotFound()) return;
        $notFound = $localResult->getNotFound();
        $remoteBuilders = $this->remoteManager->getCatalog();

        $needLoad = [];
        foreach ($remoteBuilders as $sourceId => $builder) {
            $sourceResult = $builder->buildPackagesTree($notFound);
            if ($sourceResult->hasFound())
                $needLoad[$sourceId] = $sourceResult->getFound();

            if ($sourceResult->hasNotFound()) {
                $notFound = $sourceResult->getNotFound();
            } else {
                $notFound = null;
                break;
            }
        }

        if (!empty($notFound)) {
            foreach ($notFound as $name => $version)
                echo "Package {$name}:({$version}) not found\n";
            throw new Exception("Some packages not found");
        }

        foreach ($needLoad as $sourceId => $packages) {
            $count = count($packages);
            echo "Download {$count} packages from {$sourceId}}\n";
            $tarFile = $this->remoteManager->downloadFrom($sourceId, $packages);
            $this->importPackages($tarFile);
            $tarFile->delete();
        }
    }

    private function importPackages(File $tarFile): void
    {
        $directory = $this
            ->tmp
            ->createTmpDirectory()
            ->extractPhar(new PharData($tarFile->getPath()));
        foreach ($directory->getFiles() as $path)
            $this->storage->import($path);
        $directory->delete();
    }
}