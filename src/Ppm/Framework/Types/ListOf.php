<?php

namespace Ppm\Framework\Types;

use Attribute;

#[Attribute]
class ListOf
{
    private string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}