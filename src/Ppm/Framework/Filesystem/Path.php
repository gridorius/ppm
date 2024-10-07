<?php

namespace Ppm\Framework\Filesystem;

use Phar;

class Path
{
    public static function assemblyCombine(string ...$pathPairs): string
    {
        return static::combine(dirname(Phar::running(false)), DIRECTORY_SEPARATOR, ...$pathPairs);
    }

    public static function combine(string ...$parts): string
    {
        return implode("/", $parts);
    }
}