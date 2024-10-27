<?php

namespace Ppm\Framework\Filesystem;

use Exception;
use Phar;

class Directory extends FromPath
{
    public static function createDirectory(string $path, int $permission = 0755, bool $recursive = true): string
    {
        if (!is_dir($path))
            mkdir($path, $permission, $recursive);
        return $path;
    }

    public static function getDirectoryFiles(string $path): array
    {
        return PathUtils::scanDirectory($path);
    }

    public static function clearDirectory(string $path): void
    {
        foreach (static::getDirectoryFiles($path) as $file)
            unlink($file);
    }

    public function getFiles(): array
    {
        return static::getDirectoryFiles($this->path);
    }

    public function glob(string $pattern, int $flags = 0): array
    {
        return glob($this->path . DIRECTORY_SEPARATOR . $pattern, $flags);
    }

    public function clear(): static
    {
        static::clearDirectory($this->path);
        return $this;
    }

    public function create(int $permission = 0755, bool $recursive = true): static
    {
        static::createDirectory($this->path, $permission, $recursive);
        return $this;
    }

    public function copyFileFrom(string $fromPath, string $fileName): static
    {
        copy($fromPath, $this->path . DIRECTORY_SEPARATOR . $fileName);
        return $this;
    }

    public function copyFiles(array $links): void
    {
        foreach ($links as $localPath => $realPath) {
            $outPath = $this->path . DIRECTORY_SEPARATOR . $localPath;
            $outPathDirectory = dirname($outPath);
            static::createDirectory($outPathDirectory);
            if (!copy($realPath, $outPath))
                throw new Exception("Failed to copy the file: {$realPath}");
        }
    }

    public function getFile(string $name): File
    {
        return File::from($this->path . DIRECTORY_SEPARATOR . $name);
    }

    public function createPhar(string $name): Phar
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $name . '.phar';
        if (file_exists($path))
            unlink($path);
        return new Phar($path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function combine(string ...$parts): string
    {
        return Path::combine($this->path, ...$parts);
    }

    public function extractPhar(Phar $phar, array $ignoreNames = []): static
    {
        $files = null;
        if (!empty($ignoreNames)) {
            $files = [];
            foreach ($phar as $info)
                if (!in_array($info->getFileName(), $ignoreNames))
                    $files[] = $info->getFileName();
        }
        $phar->extractTo($this->path, $files, true);
        return $this;
    }

    public function delete(): void
    {
        $files = PathUtils::scanDirectory($this->path);
        foreach ($files as $path)
            unlink($path);

        rmdir($this->path);
    }
}