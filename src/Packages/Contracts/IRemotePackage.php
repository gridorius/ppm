<?php

namespace Packages\Contracts;

interface IRemotePackage extends IPackage
{
    /**
     * @return IRemotePackage[]
     */
    public function getDepends(): array;

    public function getSource(): ISource;
}