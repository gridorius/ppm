<?php

namespace Builder\Actions;

use Assembly\FileScanner;
use Utils\PathUtils;

class CopyAction implements IAction
{
    private string $from;
    private string $to;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function setDirectories(string $buildDirectory, string $outDirectory): void
    {
        $this->from = ActionReplaceUtils::replacePaths($buildDirectory, $outDirectory, $this->from);
        $this->to = ActionReplaceUtils::replacePaths($buildDirectory, $outDirectory, $this->to);
    }

    public function run(): void
    {
        if (is_file($this->from)) {
            PathUtils::createDirectory(dirname($this->to));
            copy($this->from, $this->to);
            $this->log($this->from, $this->to);
        } elseif (is_dir($this->from)) {
            PathUtils::createDirectory($this->to);
            $offset = strlen($this->from) + 1;
            $files = FileScanner::scanDirectory($this->from);
            foreach ($files as $path)
                $this->copyFileToDirectory($path, $this->to, $offset);
        } else {
            if (!is_dir($this->to))
                mkdir($this->to, 0755, true);
            $offset = strlen($this->from) + 1;
            $files = FileScanner::scanDirectory($this->from);
            foreach ($files as $path)
                if (fnmatch($this->from, $path))
                    $this->copyFileToDirectory($path, $this->to, $offset);
        }
    }

    private function log(string $from, string $to): void
    {
        echo "Copy from {$from} to {$to}\n";
    }

    private function copyFileToDirectory(string $path, string $to, int $offset): void
    {
        $relativePath = substr($path, $offset);
        $toPath = $to . DIRECTORY_SEPARATOR . $relativePath;
        $directory = dirname($toPath);
        PathUtils::createDirectory($directory);
        copy($path, $toPath);
        $this->log($path, $toPath);
    }
}