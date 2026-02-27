<?php

namespace Ppm\Framework\Storage;

class FileStorage extends StorageBase
{
    protected string $path;

    public function __construct(string $path)
    {
        parent::__construct();
        $this->path = $path;
        $this->load();
    }

    protected function onUpdateValue(string $key, $value): void
    {
        $this->save();
    }

    public function load(): static
    {
        if (!is_file($this->path)) {
            $this->data = [];
            $this->save();
        } else {
            $this->data = json_decode(file_get_contents($this->path), true) ?? [];
        }
        return $this;
    }

    protected function save(): static
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
        return $this;
    }
}