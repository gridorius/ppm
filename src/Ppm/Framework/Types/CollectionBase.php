<?php

namespace Ppm\Framework\Types;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;

class CollectionBase implements JsonSerializable, IteratorAggregate
{
    protected array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public static function from(array $items): self
    {
        return new static($items);
    }

    public function contains($value): bool
    {
        return in_array($value, $this->items);
    }

    public function forEach($callback): static
    {
        foreach ($this->items as $key => $item)
            $callback($item, $key);

        return $this;
    }

    public function keys(): static
    {
        return new static(array_keys($this->items));
    }

    public function values(): static
    {
        return new static(array_values($this->items));
    }

    public function merge(array $data): static
    {
        return new static(array_merge($this->items, $data));
    }

    public function mergeRecursive(array $data): static
    {
        return new static(array_merge_recursive($this->items, $data));
    }

    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->items, array_keys($this->items)));
    }

    public function any(callable $callback): bool
    {
        foreach ($this->items as $key => $item)
            if ($callback($item, $key))
                return true;

        return false;
    }

    public function all(callable $callback): bool
    {
        foreach ($this->items as $key => $item)
            if (!$callback($item, $key))
                return false;

        return true;
    }

    public function column($columnKey, $indexKey = null): static
    {
        return new static(array_column($this->items, $columnKey, $indexKey));
    }

    public function join($separator): string
    {
        return implode($separator, $this->items);
    }

    public function filter(callable $callback): self
    {
        return new static(array_filter($this->items, $callback));
    }

    public function reduce(callable $callback, $initial = null): self
    {
        return new static(array_reduce($this->items, $callback, $initial));
    }

    public function find(callable $callback)
    {
        foreach ($this->items as $key => $item)
            if ($callback($item, $key))
                return $item;

        return null;
    }

    public function intersectKey(...$keys): self
    {
        $result = array_intersect_key($this->items, ...$keys);
        return new static($result);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function chunk($length, $preserveKeys = false): self
    {
        return (new static(array_chunk($this->items, $length, $preserveKeys)))->map([static::class, 'from']);
    }

    public function toJson($flags = 0): string
    {
        return json_encode($this->items, $flags);
    }

    public function length(): int
    {
        return count($this->items);
    }

    //iteratorAggregate
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    //JsonSerialize
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}