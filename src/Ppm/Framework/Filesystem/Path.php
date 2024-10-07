<?php

namespace Ppm\Framework\Filesystem;

use Phar;

class Path
{
    public static function assemblyCombine(string ...$pathPairs): string
    {
        $assemblyDirectory = dirname(Phar::running(false));
        return empty($pathPairs) ? $assemblyDirectory : static::combine($assemblyDirectory, ...$pathPairs);
    }

    public static function combine(string ...$parts): string
    {
        return implode("/", $parts);
    }
}