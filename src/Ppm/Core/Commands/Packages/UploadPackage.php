<?php

namespace Ppm\Core\Commands\Packages;

use Builder\Configuration\ConfigurationCollector;
use Exception;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;
use Ppm\Packages\PackageUtils;

class UploadPackage extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $sourcePath = $parameters['source'];
        $name = $parameters['name'];
        $version = $parameters['version'];

        $manager = new PackagesManager();
        $globalPackages = $manager->getStorage();
        $remoteManager = $manager->getRemoteManager();
        $sources = $manager->getSources();
        $package = PackageUtils::makePackageName($name, $version);
        if (!$globalPackages->exists($package))
            throw new Exception("Package {$name}:{$version} not found in local registry");

        $source = $sources->has($sourcePath) ? $sources->get($sourcePath) : $sources->createSource($sourcePath);
        $remoteManager->upload($globalPackages->get($package), $source);
    }
}