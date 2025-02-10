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
        $this->bindings = [];
        $this->running = true;
        $this->addBindings($bindings);
    }

    public static function single(StreamReadActionBind $bind): static
    {
        return new static([$bind]);
    }

    public function addBinding(StreamReadActionBind $binding): static
    {
        $id = spl_object_id($binding);
        if (!key_exists($id, $this->bindings))
            $this->bindings[$id] = $binding;
        return $this;
    }

    public function addBindings(array $bindings): static
    {
        foreach ($bindings as $binding)
            $this->addBinding($binding);
        return $this;
    }

    public function setBindings(array $bindings): static
    {
        $this->bindings = [];
        $this->addBindings($bindings);
        return $this;
    }

    public function watch( int $microseconds = 0): void
    {
        $this->bindings = array_filter($this->bindings, function ($binding) {
            return $binding->isActive();
        });
        SelectUtils::callBindings($this->bindings, $microseconds);
    }

    public function start(int $microseconds = 0): void
    {
        while ($this->running)
            $this->watch($microseconds);
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function isEmpty(): bool
    {
        return empty($this->bindings);
    }

    public function clear(): static
    {
        $this->bindings = [];
        return $this;
    }
}