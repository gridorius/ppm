<?php

namespace Ppm\Framework\Filesystem;

class TmpManager extends Directory
{
    public function createFile(string $extension, string $content): File
    {
        return $this->getTmpFile($extension)->setContent($content);
    }

    public function getTmpFile(string $extension): File
    {
        return $this->getFile(uniqid('tmp_') . '.' . $extension);
    }

    public function createTmpDirectory(): Directory
    {
        return Directory::from($this->path.DIRECTORY_SEPARATOR.uniqid('tmp_dir_'))->create();
    }
}