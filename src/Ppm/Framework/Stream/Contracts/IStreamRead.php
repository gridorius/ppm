<?php

namespace Ppm\Framework\Stream\Contracts;

interface IStreamRead
{
    public function read(int $length = 1024): string;

    public function readLine(): string;

    public function readAll(): string;

    public function hasContent(): bool;

    public function eof(): bool;
}