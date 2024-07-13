<?php

namespace Packages;

use Packages\Contracts\IUnpack;

class LocalPackage extends \PpmRegistry\LocalPackage implements IUnpack
{
    public function unpack(string $outDirectory): void
    {
        $this->phar->extractTo($outDirectory, null, true);
    }
}