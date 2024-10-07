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
}