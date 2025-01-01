<?php

namespace Ppm\Framework\Stream;

use Closure;
use Ppm\Framework\Stream\Contracts\IStream;
use Ppm\Framework\Stream\Contracts\IStreamRead;

class StreamReader implements IStreamRead
{
    protected IStream $stream;
    protected Closure $_onContent;
    protected Closure $_onEmptyContent;

    public function __construct(IStream $stream)
    {
        $this->stream = $stream;
        $this->_onContent = function (){};
        $this->_onEmptyContent = function (){};
    }

    public function onEmptyContent(callable $callback): static
    {
        $this->_onEmptyContent = Closure::fromCallable($callback);
        return $this;
    }

    public function onContent(callable $callback): static
    {
        $this->_onContent = Closure::fromCallable($callback);
        return $this;
    }

    public function read(int $length = 1024): string
    {
        $data = $this->stream->read($length);
        if (empty($data))
            call_user_func($this->_onEmptyContent);
        else
            call_user_func($this->_onContent, $data);
        return $data;
    }

    public function readLine(): string
    {
        $data = $this->stream->readLine();
        if (empty($data))
            call_user_func($this->_onEmptyContent);
        else
            call_user_func($this->_onContent, $data);
        return $data;
    }

    public function readAll(): string
    {
        $data = $this->stream->readAll();
        if (empty($data))
            call_user_func($this->_onEmptyContent);
        else
            call_user_func($this->_onContent, $data);
        return $data;
    }

    public function hasContent(): bool
    {
        return $this->stream->hasContent();
    }

    public function eof(): bool
    {
        return $this->stream->eof();
    }
}