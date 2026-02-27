<?php

namespace Ppm\Packages\Common;

class PackageUtils
{
    public static function makePackageName(string $name, string $version): string
    {
        return $name . '_' . $version;
    }

    public static function makePackagePharName(string $name, string $version): string
    {
        return static::makePackageName($name, $version) . '.phar';
    }

    public static function parsePackageName(string $path): array
    {
        return explode('_', pathinfo($path, PATHINFO_FILENAME));
    }
}