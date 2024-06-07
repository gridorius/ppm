<?php

namespace Packages;

use Packages\Contracts\IRemotePackage;
use Packages\Contracts\ISource;

class RemotePackage extends PackageBase implements IRemotePackage
{

    private ISource $source;

    /**
     * @var IRemotePackage[]
     */
    private array $depends;

    /**
     * @param string $name
     * @param string $version
     * @param array $depends
     */
    public function __construct(string $name, string $version, ISource $source, array $depends)
    {
        parent::__construct($name, $version);
        $this->source = $source;
        $this->depends = [];
        foreach ($depends as $depend)
            $this->depends[] = new RemotePackage($depend['name'], $depend['version'], $source, $depend['depends']);
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