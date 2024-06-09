<?php

namespace Terminal;

class ShellStyleParser
{
    private static array $replacement = [
        'b' => 1,
        'l' => 2,
        'red' => 31,
        'green' => 32,
        'blue' => 34,
        'purple' => 35,
        'gray' => 37
    ];

    private static string $styleRegex = "/<s\s+style='\s*(?<styles>.+?)\s*'\s*>(?<content>.+?)<\/s>/";

    public static function style(string $data)
    {
        return preg_replace_callback(static::$styleRegex, function ($matches) {
            $styles = explode(',', $matches['styles']);
            if (key_exists($styles[0], static::$replacement)) {
                return "\033[" . implode(';', array_map(function ($style) {
                        return static::$replacement[$style];
                    }, $styles)) . "m" . $matches['content'] . "\033[0m";
            } else {
                return $matches[0];
            }
        }, $data);
    }
}