<?php

namespace Builder\Actions;

use Assembly\FileScanner;

class DeleteAction implements IAction
{
    private ?string $file;
    private ?string $directory;

    public function __construct(?string $file, ?string $directory)
    {
        $this->file = $file;
        $this->directory = $directory;
        if (is_null($this->file) && is_null($this->directory))
            throw new \Exception("Incorrect arguments for delete action");
    }

    public function setDirectories(string $buildDirectory, string $outDirectory): void
    {
        $this->file = ActionReplaceUtils::replacePaths($buildDirectory, $outDirectory, $this->file);
        $this->directory = ActionReplaceUtils::replacePaths($buildDirectory, $outDirectory, $this->directory);
    }

    public function run(): void
    {
        if (!is_null($this->file)) {
            unlink($this->file);
            $this->log($this->file);
        } else {
            $files = FileScanner::scanDirectory($this->directory);
            foreach ($files as $path) {
                unlink($path);
                $this->log($path);
            }
        }
    }

    private function log(string $path): void
    {
        echo "Deleted {$path}\n";
    }
}