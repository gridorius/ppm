<?php

namespace Ppm\Framework\Storage;

use ArrayAccess;
use Iterator;
use Ppm\Framework\Traits\Observable;

class StorageArray implements Iterator, ArrayAccess
{
    use Observable;

    private array $data;

    public function __construct(array &$data)
    {
        $this->data = &$data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function get(string $key)
    {
        return $this->data[$key];
    }

    public function set($key, $value): void
    {
        $this->data[$key] = $value;
        $this->update();
    }

    public function add($value): void
    {
        $this->data[] = $value;
        $this->update();
    }

    public function delete(string $key): void
    {
        unset($this->data[$key]);
        $this->update();
    }

    public function push(...$values): void
    {
        array_push($this->data, ...$values);
        $this->update();
    }

    public function merge(array $values): void
    {
        $this->data = array_merge($this->data, $values);
        $this->update();
    }

    public function has(string $key): bool
    {
        return key_exists($key, $this->data);
    }

    // implementation

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->delete($offset);
    }

    public function current(): mixed
    {
        return current($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    public function key(): string|int|null
    {
        return key($this->data);
    }

    public function valid(): bool
    {
        return key($this->data) !== null;
    }

    public function rewind(): void
    {
        reset($this->data);
    }
}