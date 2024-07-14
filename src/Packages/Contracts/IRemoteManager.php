<?php

namespace Packages\Contracts;

use Builder\Configuration\Contracts\IConfigurationCollection;

interface IRemoteManager
{
    /**
     * @param string $name
     * @param string $version
     * @return IRemotePackage|null
     */
    public function find(string $name, string $version): ?IRemotePackage;

    public function upload(ILocalPackage $localPackage, ISource $source): void;

    public function download(IRemotePackage $remotePackage): ILocalPackage;

    public function restore(IConfigurationCollection $configurationCollection): void;
}