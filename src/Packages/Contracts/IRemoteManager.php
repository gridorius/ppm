<?php

namespace Packages\Contracts;

use Builder\Configuration\Contracts\IConfigurationCollection;

interface IRemoteManager
{
    /**
     * @param string $name
     * @param string $version
     * @return IRemotePackage[]
     */
    public function find(string $name, string $version): ?IRemotePackage;

    public function upload(ILocalPackage $localPackage, ISource $source): void;

    public function download(IRemotePackage $remotePackage): void;

    public function restore(IConfigurationCollection $configurationCollection): void;
}