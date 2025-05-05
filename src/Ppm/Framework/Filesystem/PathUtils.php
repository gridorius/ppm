<?php

namespace Ppm\Framework\Filesystem;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PathUtils
{
    public static function scanDirectory(string $directory): array
    {
        $dirIterator = new RecursiveDirectoryIterator(
            $directory,
            FilesystemIterator::CURRENT_AS_PATHNAME
            | FilesystemIterator::KEY_AS_PATHNAME
            | FilesystemIterator::SKIP_DOTS
        );
        $iterator = new RecursiveIteratorIterator($dirIterator);
        $files = [];
        foreach ($iterator as $path) {
            $files[] = $path;
        }
        return $files;
    }

    public static function resolveRelativePath(string $current, string $additional): string
    {
        if (str_starts_with($additional, '/') || preg_match("/^[a-zA-Z]:/", $additional))
            return $additional;

        $prefix = str_starts_with('/', $current) ? '/' : (preg_match("/^[a-zA-Z]:/", $current) ? '' : '/');
        $regex = '/[\\\\\/]/';
        $parts = array_merge(preg_split($regex, $current), preg_split($regex, $additional));
        $result = [];

        foreach ($parts as $part) {
            $part = trim($part);
            switch ($part) {
                case '.':
                    break;
                case '..':
                    if (count($result) > 1)
                        array_pop($result);
                    break;
                default:
                {
                    if (!empty($part))
                        $result[] = $part;
                }
            }
        }
        return $prefix . implode('/', $result);
    }

    public static function parseJson(string $path, bool $useEnv = false)
    {
        if (!file_exists($path))
            throw new Exception("File {$path} not exists");

        $content = file_get_contents($path);
        if ($useEnv)
            $content = preg_replace_callback("/\\$\{(?<var>[^\}]+?)\}/", function ($matches) {
                return getenv($matches['var']);
            }, $content);

        $data = json_decode($content, true);
        if (is_null($data))
            throw new Exception("JSON parse error in {$path}: " . json_last_error_msg());

        return $data;
    }
}