<?php

namespace Ppm\Framework\Filesystem;

use Exception;
use Phar;

/**
 * Класс для работы с директориями
 */
class Directory extends PathFromBase
{
    /**
     * Создает пустую директорию
     * @param string $path путь
     * @param int $permission права
     * @param bool $recursive создавать родительские дитректории
     * @return string
     */
    public static function createDirectory(string $path, int $permission = 0755, bool $recursive = true): string
    {
        if (!is_dir($path))
            mkdir($path, $permission, $recursive);
        return $path;
    }

    /**
     * Возвращает массив путей файлов внутри директории и вложенных директорий
     * @param string $path путь до директории
     * @return array массив путей файлов
     */
    public static function getDirectoryFiles(string $path): array
    {
        return PathUtils::scanDirectory($path);
    }

    /**
     * Удаляет содержимое директории
     * @param string $path путь до директории
     * @return void
     */
    public static function clearDirectory(string $path): void
    {
        foreach (static::getDirectoryFiles($path) as $file)
            unlink($file);
    }

    /**
     * Возвращает массив путей файлов внутри директории и вложенных директорий
     * @return array массив путей файлов
     */
    public function getFiles(): array
    {
        return static::getDirectoryFiles($this->path);
    }

    /**
     * Возвращает результат функции glob на текущей директурии
     * @param string $pattern паттерн
     * @param int $flags флаги
     * @return array массив путей
     */
    public function glob(string $pattern, int $flags = 0): array
    {
        return glob($this->path . DIRECTORY_SEPARATOR . $pattern, $flags);
    }

    /**
     * Удаляет содержимое директории
     * @return $this
     */
    public function clear(): static
    {
        static::clearDirectory($this->path);
        return $this;
    }

    /**
     * Создает пустую директорию
     * @param int $permission права
     * @param bool $recursive создавать родительские дитректории
     * @return $this
     */
    public function create(int $permission = 0755, bool $recursive = true): static
    {
        static::createDirectory($this->path, $permission, $recursive);
        return $this;
    }

    /**
     * Копирует файл в текущую директорию с указанным именем
     * @param string $fromPath путь до файла
     * @param string $fileName имя файла в директории
     * @return $this
     */
    public function copyFileFrom(string $fromPath, string $fileName): static
    {
        copy($fromPath, $this->path . DIRECTORY_SEPARATOR . $fileName);
        return $this;
    }

    /**
     * Копирует файлы в текущую директорию с учетом вложенных директорий
     * @param array $links массив путей файлов
     * @return void
     * @throws Exception
     */
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

    /**
     * Возвращает экземпляр класса \Ppm\Framework\Filesystem\File
     * @param string $name имя файла в текущей директоории
     * @return File
     */
    public function getFile(string $name): File
    {
        return File::from($this->path . DIRECTORY_SEPARATOR . $name);
    }

    /**
     * Создает phar апрхив в текущей директории
     * @param string $name имя архива
     * @return Phar
     */
    public function createPhar(string $name): Phar
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $name . '.phar';
        if (file_exists($path))
            Phar::unlinkArchive($path);
        return new Phar($path);
    }

    public function getPhar(string $name): Phar
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $name . '.phar';
        if (!file_exists($path))
            throw new Exception("Failed to get the phar: {$name}");
        return new Phar($path);
    }

    /**
     * Возвращает путь до текущей директории
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Возвращает путь относительно некущей директории
     * @param string ...$parts составные пути
     * @return string
     */
    public function combine(string ...$parts): string
    {
        return Path::combine($this->path, ...$parts);
    }

    /**
     * Разорхивирует phar-архив в текущую директорию
     * @param Phar $phar
     * @param array $ignoreNames массив имен файлов которые не будут распаковываться
     * @return $this
     */
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

    /**
     * Удаляет тукущую директорию
     * @return void
     */
    public function delete(): void
    {
        $files = PathUtils::scanDirectory($this->path);
        foreach ($files as $path)
            unlink($path);

        rmdir($this->path);
    }
}