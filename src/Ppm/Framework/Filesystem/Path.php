<?php

namespace Ppm\Framework\Filesystem;

use Phar;

/**
 * Класс для комбинации путей относительно текущей сборки
 */
class Path
{
    /**
     * Строит путь относительно директории сборки
     * @param string ...$pathPairs составные пути
     * @return string
     */
    public static function assemblyCombine(string ...$pathPairs): string
    {
        $assemblyDirectory = dirname(Phar::running(false));
        return empty($pathPairs) ? $assemblyDirectory : static::combine($assemblyDirectory, ...$pathPairs);
    }

    /**
     * Строит путь из составных частей
     * @param string ...$parts составные пути
     * @return string
     */
    public static function combine(string ...$parts): string
    {
        return implode("/", $parts);
    }
}