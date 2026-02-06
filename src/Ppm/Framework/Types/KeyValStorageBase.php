<?php

namespace Ppm\Framework\Types;

abstract class KeyValStorageBase
{

    abstract public function setTTL(string $name, int $ttl): void;

    abstract public function getTTL(string $name): string;

    abstract public function delete(string $name): void;

    abstract function __get(string $name);

    abstract function __set(string $name, $value);
}