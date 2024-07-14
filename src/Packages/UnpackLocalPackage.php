<?php

namespace Packages;

use Packages\Contracts\IUnpack;
use Utils\FileUtils;

class UnpackLocalPackage extends LocalPackage implements IUnpack
{
    public function unpack(string $outDirectory): void
    {
        FileUtils::copyDirectory($this->path, $outDirectory, '*' . Metadata::METADATA_FILE_NAME);
    }
}