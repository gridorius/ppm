<?php

namespace Packages\Contracts;

interface IUnpack extends ILocalPackage
{
    public function unpack(string $outDirectory): void;
}