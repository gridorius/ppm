<?php

namespace Packages\Contracts;

interface ILocalPackage extends IPackage
{
    public function getPath(): string;

    public function getDepends(): array;

    public function unpack(string $outDirectory): void;
}