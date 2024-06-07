<?php

namespace Utils;

use Exception;

class PathUtils
{
    public static function resolveRelativePath(string $currentPath, string $relativePath)
    {
        if (WIN) {
            $currentPath = static::preparePathForWindows($currentPath);
            $relativePath = static::preparePathForWindows($relativePath);

            if (substr($relativePath, 1, 1) == ':')
                return $relativePath;
        } else {
            if (substr($relativePath, 0, 1) == '/')
                return $relativePath;
        }

        $backCount = substr_count($relativePath, '..');
        if ($backCount > 0) {
            $postfix = substr($relativePath, 3 * $backCount);
            $prefix = $currentPath;
            for ($i = 0; $i < $backCount; $i++)
                $prefix = substr($prefix, 0, strrpos($prefix, DIRECTORY_SEPARATOR));
            return $prefix . DIRECTORY_SEPARATOR . $postfix;
        }

        if (substr($relativePath, 0, 1) == '.') {
            if (strlen($relativePath) == 1)
                return $currentPath;

            return $currentPath . DIRECTORY_SEPARATOR . substr($relativePath, 2);
        }

        return $currentPath . DIRECTORY_SEPARATOR . $relativePath;
    }

    public static function preparePathForWindows(string $path)
    {
        return preg_replace("/\//", DIRECTORY_SEPARATOR, $path);
    }

    public static function findProj(string $path): ?string
    {
        $result = glob($path . '/*proj.json');
        if (count($result) == 0)
            throw new Exception("Configuration *.proj.json not found in directory {$path}");

        if (count($result) > 1)
            throw new Exception("There should not be more than 1 project files: {$path}");

        return $result[0] ?? null;
    }

    public static function getJson(string $path)
    {
        if (!file_exists($path))
            throw new Exception("File {$path} not exists");

        $data = json_decode(file_get_contents($path), true);
        if (is_null($data))
            throw new Exception("JSON parse error in {$path}: " . json_last_error_msg());

        return $data;
    }
}