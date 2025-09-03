<?php

namespace Ppm\Framework\Stream\Async;

use Generator;

class AsyncStreamWatcher
{
    /**
     * @var Generator[]
     */
    private bool $running;
    private array $bindings;

    public function __construct()
    {
        $this->bindings = [];
        $this->running = true;
    }

    public function addCoroutine(Generator $generator): void
    {
        $this->bindings[] = $generator;
    }

    public function watch(int $microseconds = 0): void
    {

        $this->bindings = array_filter($this->bindings, function (Generator $binding) {
            return $binding->valid();
        });
        SelectUtils::callGenerators($this->bindings, $microseconds);
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

    public function clear(): static
    {
        $this->bindings = [];
        return $this;
    }
}