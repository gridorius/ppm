<?php

namespace Terminal;

class ShellStyleParser
{
    private static array $replacement = [
        'b' => 1,
        'l' => 2,
        'e' => 0,
        'red' => 31,
        'green' => 32,
        'blue' => 34,
        'purple' => 35,
        'gray' => 37
    ];

    public static function style(string $data)
    {
        return preg_replace_callback("/<(.+?)>/", function ($matches) {
            $styles = explode(',', $matches[1]);
            return "\033[" . implode(';', array_map(function ($style) {
                    return static::$replacement[$style];
                }, $styles)) . "m";
        }, $data);
    }
}