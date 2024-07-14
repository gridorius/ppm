<?php

namespace Utils;

use Assembly\FileScanner;
use Exception;

class FileUtils
{
    public static function moveFiles(array $links, string $outDirectory): void
    {
        foreach ($links as $localPath => $realPath) {
            $outPath = $outDirectory . DIRECTORY_SEPARATOR . $localPath;
            $outPathDirectory = dirname($outPath);
            if (!is_dir($outPathDirectory))
                mkdir($outPathDirectory, 0755, true);
            if (!copy($realPath, $outPath))
                throw new Exception("Failed to copy the file: {$realPath}");
        }
    }

    public static function removeDirectory(string $directory): void
    {
        $files = FileScanner::scanDirectory($directory);
        foreach ($files as $path)
            unlink($path);
        rmdir($directory);
    }

    public static function copyDirectory(string $directory, string $toDirectory, string $exclude = null): void
    {
        $offset = strlen($directory) + 1;
        $files = FileScanner::scanDirectory($directory);
        foreach ($files as $path) {
            if (is_null($exclude) || !fnmatch($exclude, $path)) {
                $relativePath = substr($path, $offset);
                $newPath = $toDirectory . DIRECTORY_SEPARATOR . $relativePath;
                PathUtils::createDirectory(dirname($newPath));
                copy($path, $newPath);
            }
        }
    }
}