<?php

namespace Utils;

use Exception;

class FileUtils
{
    public static function moveFiles(array $links, string $outDirectory)
    {
        foreach ($links as $localPath => $realPath) {
            $outPath = $outDirectory . DIRECTORY_SEPARATOR . $localPath;
            $outPathDirectory = dirname($outPath);
            if (!is_dir($outPathDirectory))
                mkdir($outPathDirectory, 0755, true);
            if (!copy($realPath, $outPath))
                throw new Exception("Не удалось копировать файл {$realPath}");
        }
    }
}