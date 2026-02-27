<?php

namespace Ppm\Framework\Types;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class Collection extends CollectionBase
{
    public function add(...$items): self
    {
        foreach ($items as $item)
            $this->items[] = $item;

        return $this;
    }

    public function splice(int $offset, int $count = 1): static
    {
        array_splice($this->items, $offset, $count);
        return $this;
    }

    public function shift()
    {
        return array_shift($this->items);
    }

    public function pop()
    {
        return array_pop($this->items);
    }

    public function addToStart(...$values): static
    {
        array_unshift($this->items, ...$values);
        return $this;
    }

    public function first()
    {
        return reset($this->items);
    }

    public function toDictionary(callable $keyCallback): Dictionary
    {
        $dictionary = new Dictionary();
        foreach ($this->items as $item)
            $dictionary->set($keyCallback($item), $item);
        return $dictionary;
    }
}