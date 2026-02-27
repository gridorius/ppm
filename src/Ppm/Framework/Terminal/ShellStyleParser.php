<?php

namespace Ppm\Framework\Terminal;

class ShellStyleParser
{
    private static array $styles = [
        'b' => 1,
        'l' => 2,
        'f' => 5,
        'emp' => 4,
        'red' => 31,
        'green' => 32,
        'blue' => 34,
        'purple' => 35,
        'gray' => 37,
        'color' => '38;2',
        'bg' => '48;2',
    ];

//    private static string $styleRegex = "/<s\s+style='\s*(?<styles>.+?)\s*'\s*>(?<content>.*?)<\/s>/s";
    private static string $styleRegex = "/<s\s+(?<tags>.+?)\s*>(?<content>.*?)<\/s>/s";

    public static function style(string $data): string
    {
        return preg_replace_callback(static::$styleRegex, function ($matches) {
            $tagsString = preg_replace('/\s+/', ' ', $matches['tags']);
            $tagsRaw = explode(' ', $tagsString);
            $tags = [];
            foreach ($tagsRaw as $tag) {
                $options = [];
                if (str_contains($tag, "=")) {
                    [$tag, $optionsRaw] = explode('=', $tag);
                    $tag = trim($tag);
                    $options = explode(',', trim($optionsRaw, "'\""));
                }

                $tags[$tag] = $options;
            }
            $styleArgs = [];
            foreach ($tags as $tag => $options)
                if (key_exists($tag, static::$styles)) {
                    $styleArgs[] = static::$styles[$tag];
                    foreach ($options as $option)
                        $styleArgs[] = $option;
                }

            if (!empty($styleArgs)) {
                return "\033[" . implode(';', $styleArgs) . "m" . $matches['content'] . "\033[0m";
            } else {
                return $matches[0];
            }
        }, $data);
    }
}