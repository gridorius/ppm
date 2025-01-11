<?php

namespace Ppm\Framework\Stream;

use Ppm\Framework\Stream\Contracts\IStream;

class StreamWriter
{
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function addContent(string $content): static
    {
        $this->content .= $content;
        return $this;
    }

    public function getLength(): int
    {
        return strlen($this->content);
    }

    public function isEmpty(): bool
    {
        return $this->content === '';
    }

    public function writeTo(IStream $stream): void
    {
        if ($this->getLength() == 0) return;
        $written = $stream->write($this->content);
        $this->content = substr($this->content, $written);
    }
}