<?php

namespace Ppm\Framework\Stream;

use Exception;
use Ppm\Framework\Stream\Async\SelectUtils;
use Ppm\Framework\Stream\Contracts\IStream;
use Ppm\Framework\Stream\Contracts\IStreamWrite;
use Ppm\Framework\Stream\Contracts\PollType;
use Ppm\Framework\Stream\Contracts\StreamBase;

class ResourceStream extends StreamBase
{
    protected $resource;

    public function __construct($resource)
    {
        if (!is_resource($resource))
            throw new Exception("Invalid resource");

        $this->resource = $resource;
    }

    public function readAll(int $blockSize = 8192): string
    {
        $content = '';
        do {
            $block = fread($this->resource, $blockSize);
            $content .= $block;
        } while (strlen($block) > $blockSize);
        return $content;
    }

    public static function from($resource): static
    {
        return new static($resource);
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function read(int $length = 1024): string
    {
        return fread($this->resource, $length);
    }

    public function readLine(): string
    {
        return fgets($this->resource);
    }

    public function getRemainderLength(): int
    {
        return $this->getMetadata()['unread_bytes'];
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }

    public function isValid(): bool
    {
        return is_resource($this->resource);
    }

    /** @inheritdoc */
    public function block(): void
    {
        stream_set_blocking($this->resource, true);
    }

    public function unblock(): void
    {
        stream_set_blocking($this->resource, false);
    }

    public function write(string $data, int $length = null): int
    {
        if (($written = fwrite($this->resource, $data, $length)) === false)
            throw new Exception(sprintf("Unable to write (%s) bytes to stream", strlen($data)));

        return $written;
    }

    public function copyToStream(IStream $stream, int $length = null, int $offset = 0): int
    {
        $written = stream_copy_to_stream($this->resource, $stream->getResource(), $length, $offset);
        return $written ?: 0;
    }

    public function writeLine(string $data): void
    {
        $this->write($data . "\n");
    }

    public function rewind(): void
    {
        rewind($this->resource);
    }

    public function getMetadata(): array
    {
        return stream_get_meta_data($this->resource);
    }

    public function tell(): int
    {
        $position = ftell($this->resource);
        if (is_bool($position))
            throw new Exception("Invalid position");

        return $position;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (fseek($this->resource, $offset, $whence) == -1)
            throw new Exception("Unable to seek");
    }

    public function poll(int $microseconds = 0, PollType $pollType = PollType::Read): bool
    {
        $read = $write = $except = null;
        switch ($pollType) {
            case PollType::Read:
                $read = [$this->getResource()];
                break;
            case PollType::Write:
                $write = [$this->getResource()];
                break;
        }
        return stream_select($read, $write, $except, 0, $microseconds) > 0;
    }

    public function close(): void
    {
        fclose($this->resource);
    }
}