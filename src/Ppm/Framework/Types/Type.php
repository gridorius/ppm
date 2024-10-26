<?php

namespace Ppm\Framework\Types;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class Type
{
    private ReflectionClass $reflection;

    public function __construct($objectOrClass)
    {
        $this->reflection = new ReflectionClass($objectOrClass);
    }

    public static function of($objectOrClass): static
    {
        return new static($objectOrClass);
    }

    /**
     * @return ReflectionMethod[]
     */
    public function getPublicMethods(): array
    {
        return $this->reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    }

    public function getPublicNonStaticProperties(): array
    {
        return array_filter($this->reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            function (ReflectionProperty $property) {
                return !$property->isStatic();
            }
        );
    }
}