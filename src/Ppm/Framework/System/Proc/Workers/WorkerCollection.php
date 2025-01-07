<?php

namespace Ppm\Framework\System\Proc\Workers;

use ArrayAccess;
use Ppm\Framework\System\Proc\Workers\Contracts\IWorker;

class WorkerCollection implements IWorker, ArrayAccess
{
    protected $workers = [];

    public function __construct(array $workers = [])
    {
        $this->workers = $workers;
    }

    public function add(IWorker $worker): static
    {
        $this->workers[] = $worker;
        return $this;
    }

    public function onMessage(callable $callable): static
    {
        foreach ($this->workers as $worker)
            $worker->onMessage($callable);

        return $this;
    }

    public function onMessageParty(callable $callable): static
    {
        foreach ($this->workers as $worker)
            $worker->onMessageParty($callable);
        return $this;
    }

    public function send(string $message, array $headers = []): void
    {
        foreach ($this->workers as $worker)
            $worker->send($message, $headers);
    }

    public function getBindings(): array
    {
        $bindings = [];
        foreach ($this->workers as $worker)
            $bindings[] = $worker->getBind();
        return $bindings;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->workers[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->workers[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->workers[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->workers[$offset]);
    }
}