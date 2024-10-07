<?php

namespace Ppm\Framework\Stream;

use Exception;

class ResourceStream extends StreamBase
{
    protected $resource;

    public function __construct($resource)
    {
        if (!is_resource($resource))
            throw new Exception("Invalid resource");

        $this->resource = $resource;
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

    public function readToChar(string $toChar): string
    {
        $string = '';
        $length = $this->getRemainderLength();
        for($i = 0; $i < $length; $i++) {
            $char = $string .= $this->read(1);
            if ($toChar == $char) break;
        }
        return $string;
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

    public function write(string $data): void
    {
        fwrite($this->resource, $data);
    }

    public function writeLine(string $data): void
    {
        $this->write($data . PHP_EOL);
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

    public function close(): void
    {
        fclose($this->resource);
    }
}