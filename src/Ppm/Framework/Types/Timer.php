<?php

namespace Ppm\Framework\Types;

class Timer
{
    protected float $start;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->start = microtime(true);
    }

    public function getPassed(): float
    {
        return microtime(true) - $this->start;
    }

    public function getFormatPassed(): string
    {
        return number_format($this->getPassed(), 3);
    }
}