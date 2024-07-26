<?php

namespace Packages;

use Packages\Contracts\ILocalPackage;
use Phar;

class Compactor
{
    public static function compact(ILocalPackage $package, string $toDirectory, string $name = null): string
    {
        $path = $toDirectory . DIRECTORY_SEPARATOR . ($name ?? $package->getName()) . '.phar';
        $phar = new Phar($path);
        $phar->startBuffering();
        $phar->buildFromDirectory($package->getPath());
        $phar->setMetadata($package->getMetadata());
        $phar->stopBuffering();
        return $path;
    }
}