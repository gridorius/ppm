<?php

namespace Packages\Contracts;

interface IRemotePackage extends IPackage
{
    public function getDepends(): array;

    public function getSource(): ISource;
}