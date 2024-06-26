<?php

namespace Assembly;

use Phar;

class Utils
{
    public static function path(string ...$pathPairs): string
    {
        return dirname(Phar::running(false)) . DIRECTORY_SEPARATOR . implode("/", $pathPairs);
    }
}