<?php

namespace Ppm\Framework\Stream;

class StringStream extends StreamBase
{
    private string $data;
    private int $cursor = 0;

    public function __construct(string $data = '')
    {
        $this->data = $data;
    }

    public function read(int $length = 1024): string
    {
        $content = substr($this->data, $this->cursor, $length);
        $this->cursor += strlen($content);
        return $content;
    }

    public function readLine(): string
    {
        return $this->readToChar("\n");
    }

    public function readAll(): string
    {
        return $this->read($this->getRemainderLength());
    }

    public function readToChar(string $toChar): string
    {
        $string = '';
        while ($this->hasContent()) {
            $char = $string .= $this->read(1);
            if ($toChar == $char) break;
        }
        return $string;
    }

    public function getRemainderLength(): int
    {
        return strlen($this->data) - $this->cursor;
    }

    public function write(string $data): void
    {
        $this->data .= $data;
    }

    public function writeLine(string $data): void
    {
        $this->write($data . PHP_EOL);
    }

    public function eof(): bool
    {
        return $this->cursor == strlen($this->data);
    }

    public function rewind(): void
    {
        $this->cursor = 0;
    }

    public function tell(): int
    {
        return $this->cursor;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        switch ($whence) {
            case SEEK_SET:
                $this->cursor = $offset;
                break;
            case SEEK_CUR:
                $this->cursor += $offset;
                break;
            case SEEK_END:
                $this->cursor = strlen($this->data) + $offset;
                break;
        }
    }

    public function close(): void
    {
        $this->data = '';
    }
}