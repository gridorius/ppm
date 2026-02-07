<?php

namespace Ppm\Framework\Types;

use ArrayAccess;

abstract class KeyValStorageBase implements ArrayAccess
{
    abstract public function setTTL(string $name, int $ttl): void;

    abstract public function getTTL(string $name): ?int;

    abstract public function delete(string $name): void;

    abstract function __get(string $name);

    abstract function __set(string $name, $value);

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->delete($offset);
    }
}