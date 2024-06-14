<?php

namespace Builder;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileScanner
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
}