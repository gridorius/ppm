<?php

namespace Ppm\Framework\Utils;

class StringUtils
{
    public static function replace(string $haystack, array $map): string
    {
        $from = [];
        $to = array_values($map);
        foreach ($map as $key => $value)
            $from[] = "/{$key}/";

        return preg_replace($from, $to, $haystack);
    }

    public static function convertBytes(int $bytes): string
    {
        $unit = ['B', 'KB', 'MB', 'GB', 'TB'];
        return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $unit[$i];
    }
}