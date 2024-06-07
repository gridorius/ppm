<?php

namespace Packages\Contracts;

use Builder\Configuration\Contracts\IConfigurationCollection;

interface ILocalManager
{
    public function exist(string $name, string $version): bool;

    public function get(string $name, string $version): ILocalPackage;

    public function getFileName(string $name, string $version): string;

    public function findLocalPackage(string $name, string $version): string;

    public function getLocalPath(string $name, string $version): string;

    public function save(string $name, string $version, string $content): void;

    public function unpackPackagesRecursive(IConfigurationCollection $configurationCollection, string $outDirectory): void;
}