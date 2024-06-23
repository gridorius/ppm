<?php

namespace Packages\Contracts;

use PpmRegistry\Contracts\IPackage;

interface IRemotePackage extends IPackage
{
    public function getDepends(): array;

    public function getSource(): ISource;
}