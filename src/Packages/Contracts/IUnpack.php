<?php

namespace Packages\Contracts;

use PpmRegistry\Contracts\ILocalPackage;

interface IUnpack extends ILocalPackage
{
    public function unpack(string $outDirectory): void;
}