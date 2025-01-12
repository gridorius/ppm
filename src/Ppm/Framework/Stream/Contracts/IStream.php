<?php

namespace Ppm\Framework\Stream\Contracts;

interface IStream extends IStreamRead, IStreamWrite, IResourceBase
{
    public function rewind(): void;

    public function getRemainderLength(): int;

    public function tell(): int;

    public function seek(int $offset, int $whence = SEEK_SET): void;

    public function unblock(): void;
}