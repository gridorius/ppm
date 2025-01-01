<?php

namespace Ppm\Framework\Stream\Contracts;

interface IStreamWrite
{
    public function write(string $data): int;

    public function writeLine(string $data): void;
}