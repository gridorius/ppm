<?php

namespace Ppm\Framework\System\SharedMemory;

use SysvSharedMemory;

class Segment
{
    private SysvSharedMemory $memory;

    public function __construct(string $key, $permissions = 0655, ?int $size = null)
    {
        $this->memory = shm_attach(crc32($key), $size, $permissions);
    }

    public function put(string $key, $value): bool
    {
        return shm_put_var($this->memory, crc32($key), $value);
    }

    public function has(string $key): bool
    {
        return shm_has_var($this->memory, crc32($key));
    }

    public function get(string $key)
    {
        return shm_get_var($this->memory, crc32($key));
    }

    public function remove(string $key): bool
    {
        return shm_remove_var($this->memory, crc32($key));
    }

    public function close(): void
    {
        shm_remove($this->memory);
    }
}