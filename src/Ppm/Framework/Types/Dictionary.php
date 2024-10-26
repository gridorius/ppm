<?php

namespace Ppm\Framework\Types;

class Dictionary extends CollectionBase
{
    public function set(string $key, $value): static
    {
        $this->items[$key] = $value;
        return $this;
    }

    public function get(string $key)
    {
        return $this->items[$key];
    }

    public function delete($key): self
    {
        unset($this->items[$key]);
        return $this;
    }

    public function has($key): bool
    {
        return key_exists($key, $this->items);
    }
}