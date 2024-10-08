<?php

namespace Ppm\Framework\Storage;

use Ppm\Framework\Traits\Observable;

class MemoryStorage extends StorageBase
{
    use Observable;

    protected function onUpdateValue(string $key, $value): void
    {
        $this->update($key, $value);
    }
}