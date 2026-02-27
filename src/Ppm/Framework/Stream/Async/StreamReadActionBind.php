<?php

namespace Ppm\Framework\Stream\Async;

use Closure;
use Ppm\Framework\Stream\Contracts\IStream;

class StreamReadActionBind
{
    protected IStream $stream;
    protected closure $action;
    protected bool $active;

    public function __construct(IStream $stream, callable $action)
    {
        $this->stream = $stream;
        $this->action = Closure::fromCallable($action);
        $this->active = true;
    }

    public static function create(IStream $stream, callable $action): static
    {
        return new static($stream, $action);
    }

    public function setStream(IStream $stream): void
    {
        $this->stream = $stream;
    }

    public function getStream(): IStream
    {
        return $this->stream;
    }

    public function call(): void
    {
        call_user_func($this->action, $this->stream, $this);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function disable(): void
    {
        $this->active = false;
    }
}