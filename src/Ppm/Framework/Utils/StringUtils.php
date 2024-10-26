<?php

namespace Ppm\Framework\Utils;

class StringUtils
{
    public static function replace(array $from, array $to, string $haystack): string
    {
        for ($i = 0; $i < count($from); $i++)
            $from[$i] = "/{$from[$i]}/";

        return preg_replace($from, $to, $haystack);
    }

    public static function convertBytes(int $bytes): string
    {
        $unit = ['B', 'KB', 'MB', 'GB', 'TB'];
        return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $unit[$i];
    }
}