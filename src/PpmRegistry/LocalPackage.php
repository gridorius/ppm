<?php

namespace PpmRegistry;

use Phar;
use PpmRegistry\Contracts\ILocalPackage;
use PpmRegistry\Contracts\PackageBase;

class LocalPackage extends PackageBase implements ILocalPackage
{
    private string $path;

    protected Phar $phar;
    private array $depends;

    public function __construct(string $packagePath, string $name, string $version)
    {
        $this->path = $packagePath;
        $this->phar = new Phar($packagePath);
        $metadata = $this->phar->getMetadata();
        parent::__construct($name, $version);
        $this->depends = $metadata['depends'];
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDepends(): array
    {
        return $this->depends;
    }
}