<?php

namespace Utils;

use Exception;

class FileUtils
{
    public static function moveFiles(array $links, string $outDirectory){
        foreach ($links as $localPath => $realPath) {
            if (!copy($realPath, $outDirectory . DIRECTORY_SEPARATOR . $localPath))
                throw new Exception("Не удалось копировать файл {$realPath}");
        }
    }
}