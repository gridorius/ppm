<?php

namespace Packages;

use Packages\Contracts\ILocalPackage;

class LocalPackage extends PackageBase implements ILocalPackage
{
    private string $path;
    private array $depends;

    public function __construct(string $packagePath)
    {
        $this->path = $packagePath;
        $phar = new \Phar($packagePath);
        $metadata = $phar->getMetadata();
        parent::__construct($metadata['name'], $metadata['version']);
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

    public function unpack(string $outDirectory): void
    {
        $phar = new \Phar($this->path);
        $phar->extractTo($outDirectory);
    }
}