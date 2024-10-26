<?php

namespace Ppm\Framework\Utils;

class ArrayUtils
{
    public static function export(array $array): string
    {
        return preg_replace(["/array\s\(/", "/\)/"], ["[", "]"], var_export($array, true));
    }
}