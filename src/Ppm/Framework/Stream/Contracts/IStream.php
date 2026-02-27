<?php

namespace Ppm\Framework\Stream\Contracts;

interface IStream extends IStreamRead, IStreamWrite, IResourceBase
{
    public function rewind(): static;

    public function getRemainderLength(): int;

    public function tell(): int;

    public function seek(int $offset, int $whence = SEEK_SET): void;

    public function unblock(): void;

    public function block(): void;

    public function copyToStream(IStream $stream, ?int $length = null, int $offset = 0): int;

    public function poll(int $microseconds = 0, PollType $pollType = PollType::Read): bool;
}