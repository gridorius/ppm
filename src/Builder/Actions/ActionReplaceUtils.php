<?php

namespace Builder\Actions;

class ActionReplaceUtils
{
    const BUILD_DIRECTORY = "/\\$\(buildDirectory\)/";
    const OUT_DIRECTORY = "/\\$\(outDirectory\)/";

    public static function replacePaths(string $buildDirectory, string $outDirectory, string $value): string
    {
        return preg_replace([static::BUILD_DIRECTORY, static::OUT_DIRECTORY], [$buildDirectory, $outDirectory], $value);
    }
}