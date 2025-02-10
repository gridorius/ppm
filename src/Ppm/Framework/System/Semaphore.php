<?php

namespace Ppm\Framework\System;

use SysvSemaphore;

class Semaphore
{
    private SysvSemaphore $semaphore;

    public function __construct(string $key, int $max, int $permissions = 0655, bool $autorelease = true)
    {
        $this->semaphore = sem_get(crc32($key), $max, $permissions, $autorelease);
    }

    public function wait(): void
    {
        sem_acquire($this->semaphore);
    }

    public function release(): void
    {
        sem_release($this->semaphore);
    }
}