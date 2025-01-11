<?php

namespace Ppm\Framework\Types;

class OptionsBuilder
{
    /**
     * @template T
     * @param T $abstract
     * @param array $properties
     * @return T
     */
    public static function build(string $abstract, array $properties)
    {
        $nonStaticProperties = Type::of($abstract)->getPublicNonStaticProperties();
        $constructor = Type::of($abstract)->getConstructor();
        $usedProperties = [];
        if (!is_null($constructor)) {
            $arguments = [];
            $properties = array_change_key_case($properties);
            foreach ($constructor->getParameters() as $param) {
                $name = $param->getName();
                $type = $param->getType();
                $typeName = is_null($type) ? '' : $type->getName();
                $usedProperties[] = $name;
                $arguments[] = static::getPropertyValue($typeName, $name, $properties, $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
            }
            $instance = Type::of($abstract)->getReflection()->newInstanceArgs($arguments);
        } else {
            $instance = Type::of($abstract)->getReflection()->newInstance();
        }

        foreach ($nonStaticProperties as $property) {
            $name = $property->getName();
            $type = $property->getType();
            $typeName = is_null($type) ? '' : $type->getName();
            if (!in_array($name, $usedProperties)) {
                $value = static::getPropertyValue($typeName, $name, $properties, $property->getDefaultValue());
                if (!is_null($value))
                    $instance->{$property->getName()} = $value;
            }
        }
        return $instance;
    }

    protected static function getPropertyValue(string $type, string $name, array $properties, $defaultValue = null)
    {
        $value = $properties[$name] ?? $properties[strtolower($name)];
        switch ($type) {
            case 'int':
                return empty($value) ? $defaultValue : (int)$value;
            case 'string':
                return empty($value) ? $defaultValue : (string)$value;
            case 'bool':
                return empty($value) ? $defaultValue : (bool)$value;
            case 'float':
                return empty($value) ? $defaultValue : (float)$value;
            case 'array':
                return empty($value) || !is_array($value) ? $defaultValue : $value;
            default:
                if (isset($value))
                    return static::build($type, $value);
                return null;
        }
    }
}