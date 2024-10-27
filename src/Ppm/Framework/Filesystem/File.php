<?php

namespace Ppm\Framework\Filesystem;

class File extends FromPath
{
    public function getPath(): string
    {
        return $this->path;
    }

    public function setContent(string $content): static
    {
        file_put_contents($this->path, $content);
        return $this;
    }

    public function setJsonContent(mixed $object, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): static
    {
        file_put_contents($this->path, json_encode($object, $flags));
        return $this;
    }

    public function setSerializedContent(object $object): static
    {
        file_put_contents($this->path, serialize($object));
        return $this;
    }

    public function getSerializedContent(): mixed
    {
        return unserialize(file_get_contents($this->path));
    }

    public function getJsonContent(): ?array
    {
        return json_decode(file_get_contents($this->path), true);
    }

    public function delete(): void
    {
        unlink($this->path);
    }
}