<?php

namespace Ppm\Framework\Stream\Async;

class AsyncStreamWatcher
{
    /**
     * @var StreamReadActionBind[]
     */
    protected array $bindings;

    private bool $running;

    public function __construct(array $bindings = [])
    {
        $this->bindings = $bindings;
        $this->running = true;
    }

    public static function single(StreamReadActionBind $bind): static
    {
        return new static([$bind]);
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

    public function start(int $seconds = 1, int $microseconds = 0): void
    {
        while ($this->running)
            $this->watch($seconds, $microseconds);
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function clear(): static
    {
        $this->bindings = [];
        return $this;
    }
}