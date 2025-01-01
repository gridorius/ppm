<?php

namespace Ppm\Framework\Stream\Async;

class AsyncStreamsReader
{
    /**
     * @var StreamReadActionBind[]
     */
    protected array $bindings;

    public function __construct(array $bindings = [])
    {
        $this->bindings = $bindings;
    }

    public function addBinding(StreamReadActionBind $binding): static
    {
        $this->bindings[] = $binding;
        return $this;
    }

    public function setBindings(array $bindings): static
    {
        $this->bindings = $bindings;
        return $this;
    }

    public function watch(int $seconds = 1, int $microseconds = 0): void
    {
        SelectUtils::callBindings($this->bindings, $seconds, $microseconds);
    }

    public function clear(): static
    {
        $this->bindings = [];
        return $this;
    }
}