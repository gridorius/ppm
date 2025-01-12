<?php

namespace Ppm\Framework\Stream;

use Ppm\Framework\Stream\Contracts\IStream;

class StreamWriter
{
    private string $content;
    private int $position;

    public function __construct(string $content = '')
    {
        $this->content = $content;
        $this->position = 0;
    }

    public function addContent(string $content): static
    {
        $this->content .= $content;
        return $this;
    }

    public function getLength(): int
    {
        return strlen($this->content) - $this->position;
    }

    public function isEmpty(): bool
    {
        return $this->getLength() === 0;
    }

    public function writeTo(IStream $stream): void
    {
        if ($this->getLength() == 0) return;
        $this->position += $stream->write(substr($this->content, $this->position));
    }
}