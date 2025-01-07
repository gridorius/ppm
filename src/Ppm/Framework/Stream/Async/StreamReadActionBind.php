<?php

namespace Ppm\Framework\Stream\Async;

use Closure;
use Ppm\Framework\Stream\Contracts\IStream;

class StreamReadActionBind
{
    protected IStream $stream;
    protected closure $action;

    public function __construct(IStream $stream, callable $action)
    {
        $this->stream = $stream;
        $this->action = Closure::fromCallable($action);
    }

    public static function create(IStream $stream, callable $action): static
    {
        return new static($stream, $action);
    }

    public function getStream(): IStream
    {
        return $this->stream;
    }

    public function call(): void
    {
        call_user_func($this->action, $this->stream);
    }
}