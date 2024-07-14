<?php

namespace Packages;

use Packages\Contracts\IRemotePackage;
use Packages\Contracts\ISource;
use Packages\Contracts\PackageBase;

class RemotePackage extends PackageBase implements IRemotePackage
{
    private ISource $source;
    private array $depends;

    /**
     * @param string $name
     * @param string $version
     * @param ISource $source
     * @param array $depends
     */
    public function __construct(string $name, string $version, ISource $source, array $depends = [])
    {
        parent::__construct($name, $version);
        $this->source = $source;
        $this->depends = $depends;
    }

    public function getDepends(): array
    {
        return $this->depends;
    }

    public function getSource(): ISource
    {
        return $this->source;
    }
}