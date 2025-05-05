<?php

namespace Ppm\Framework\Filesystem;

/**
 * Класс для работы с файлами
 */
class File extends PathFromBase
{
    /**
     * Возвращает путь до текущего файла
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Записывает содержимое в файл
     * @param string $content содержимое
     * @return $this
     */
    public function setContent(string $content): static
    {
        file_put_contents($this->path, $content);
        return $this;
    }

    /**
     * Записывает обьект в файл как json
     * @param mixed $object обьект
     * @param int $flags json флаги
     * @return $this
     */
    public function setJsonContent(mixed $object, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): static
    {
        file_put_contents($this->path, json_encode($object, $flags));
        return $this;
    }

    /**
     * Записывает обьект в файл в сериализованном виде
     * @param object $object обьект
     * @return $this
     */
    public function setSerializedContent(object $object): static
    {
        file_put_contents($this->path, serialize($object));
        return $this;
    }

    /**
     * Считывает содержимое файла и десериализует
     * @return mixed
     */
    public function getSerializedContent(): mixed
    {
        return unserialize(file_get_contents($this->path));
    }

    /**
     * Считывает содержимое файла и декодирует json в массив
     * @return array|null
     */
    public function getJsonContent(): ?array
    {
        return json_decode(file_get_contents($this->path), true);
    }

    /**
     * Удаляет файл
     * @return void
     */
    public function delete(): void
    {
        unlink($this->path);
    }
}