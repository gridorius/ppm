<?php

namespace Packages;

use Packages\Contracts\IPackage;

abstract class PackageBase implements IPackage
{
    private string $name;
    private string $version;

    /**
     * @param string $name
     * @param string $version
     */
    public function __construct(string $name, string $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}