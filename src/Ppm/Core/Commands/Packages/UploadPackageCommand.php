<?php

namespace Ppm\Core\Commands\Packages;

use Exception;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;
use Ppm\Packages\PackageUtils;

class UploadPackageCommand extends CommandBase
{
    public function getDescription(): string
    {
        return "Upload package to source";
    }

    public function execute(array $parameters, array $options): void
    {
        $sourcePath = $parameters['source'];
        $name = $parameters['name'];
        $version = $parameters['version'];

        $manager = new PackagesManager();
        $storage = $manager->getStorage();
        $remoteManager = $manager->getRemoteManager();
        $sources = $manager->getSources();
        if (!($package = $storage->get($name, $version)))
            throw new Exception("Package {$name}:{$version} not found in local registry");

        $source = $sources->has($sourcePath) ? $sources->get($sourcePath) : $sources->createSource($sourcePath);
        $remoteManager->upload($package->getPath(), $source);
    }
}