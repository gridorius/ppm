<?php

namespace Ppm\Framework\Stream;

interface IStream
{
    public function read(int $length = 1024): string;

    public function readLine(): string;

    public function readAll(): string;

    public function readToChar(string $toChar): string;

    public function write(string $line): void;

    public function writeLine(string $data): void;

    public function eof(): bool;

    public function rewind(): void;

    public function hasContent(): bool;

    public function getRemainderLength(): int;

    public function tell(): int;

    public function seek(int $offset, int $whence = SEEK_SET): void;
}