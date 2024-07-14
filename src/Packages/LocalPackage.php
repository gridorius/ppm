<?php

namespace Packages;

use Packages\Contracts\ILocalPackage;
use Packages\Contracts\PackageBase;

class LocalPackage extends PackageBase implements ILocalPackage
{
    protected string $path;
    protected array $depends;
    protected array $metadata;

    public function __construct(string $packagePath, string $name, string $version)
    {
        parent::__construct($name, $version);
        $this->path = $packagePath;
        $this->metadata = include $packagePath . DIRECTORY_SEPARATOR . Metadata::METADATA_FILE_NAME;
        $this->depends = $this->metadata['depends'];
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getProjectPharPath(): string
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . $this->getName() . '.phar';
    }

    public function getDepends(): array
    {
        return $this->depends;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}