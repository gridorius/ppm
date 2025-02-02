<?php

namespace Ppm\Framework\Stream\Contracts;

interface IStreamWrite
{
    public function write(string $data, int $length = null): int;

    public function writeLine(string $data): void;
}