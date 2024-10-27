<?php

namespace Ppm\Builder;

use Exception;

class ProjectFile
{
    /**
     * return project file from directory or throw
     *
     * @param string $path
     * @return string
     * @throws Exception
     */
    public static function getPathOrThrow(string $path): string
    {
        $result = glob($path . '/*proj.json');
        if (count($result) == 0)
            throw new Exception("Configuration *.proj.json not found in directory {$path}");

        if (count($result) > 1)
            throw new Exception("There should not be more than 1 project files: {$path}");

        return $result[0];
    }

    /**
     * return project file from directory or null
     *
     * @param string $path
     * @return string|null
     */
    public static function getPathOrNull(string $path): ?string
    {
        $result = glob($path . '/*proj.json');
        return $result[0] ?? null;
    }
}