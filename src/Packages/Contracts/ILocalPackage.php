<?php

namespace Packages\Contracts;

interface ILocalPackage extends IPackage
{
    public function getPath(): string;

    public function getProjectPharPath(): string;

    public function getDepends(): array;

    public function getMetadata(): array;
}