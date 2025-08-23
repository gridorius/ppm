<?php

namespace Ppm\Framework\Types;

use Attribute;

#[Attribute]
class DictionaryOf
{
    private string $className;
    private ?string $key;

    public function __construct(string $className, ?string $key = null)
    {
        $this->className = $className;
        $this->key = $key;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }
}