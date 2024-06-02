<?php

namespace Utils;

class Timer
{
    protected float $start;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void{
        $this->start = microtime(true);
    }

    public function getPassed(): string{
        return number_format(microtime(true) - $this->start, 3);
    }
}