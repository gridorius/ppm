<?php

namespace Utils;

class ReplaceUtils
{
    public static function replace(array $from, array $to, string $haystack): string{
        for($i = 0; $i < count($from); $i++)
            $from[$i] = "/{$from[$i]}/";

        return preg_replace($from, $to, $haystack);
    }

    public static function prepareLF(string $data): string{
        return preg_replace("/\r/", '', $data);
    }
}