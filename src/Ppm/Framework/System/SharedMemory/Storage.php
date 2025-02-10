<?php

namespace Ppm\Framework\System\SharedMemory;

class Storage
{
    public static function open(string $key, $permissions = 0655, int $size = null): Segment
    {
        $memory = shm_attach(crc32($key), $size, $permissions);
        return new Segment($memory);
    }
}