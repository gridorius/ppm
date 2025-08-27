<?php

namespace Ppm\Framework\Types;

use Exception;
use Ppm\Framework\Exceptions\OptionParseException;
use Ppm\Framework\Exceptions\OptionsBuildException;

class OptionsBuilder
{
    /**
     * @template T
     * @param string $abstract
     * @param array $properties
     * @return T
     * @throws OptionsBuildException
     * @throws \ReflectionException
     */
    public static function build(string $abstract, array $properties, string $topField = '')
    {
        $errors = [];
        $nonStaticProperties = Type::of($abstract)->getPublicNonStaticProperties();
        $constructor = Type::of($abstract)->getConstructor();
        $usedProperties = [];
        $properties = array_change_key_case($properties);
        if (!is_null($constructor)) {
            $arguments = [];
            foreach ($constructor->getParameters() as $param) {
                $name = $param->getName();
                $type = $param->getType();
                $typeName = is_null($type) ? '' : $type->getName();
                $usedProperties[] = $name;
                $fieldPath = $topField . '.' . $name;
                try {
                    $value = static::getPropertyValue($typeName, $name, $properties, null, null, $fieldPath);
                    if (is_null($value)) {
                        if ($param->isDefaultValueAvailable())
                            $value = $param->getDefaultValue();
                        elseif ($param->allowsNull())
                            $value = null;
                        else
                            $errors[$fieldPath] = ['null', $typeName];
                    }
                    $arguments[] = $value;
                } catch (OptionParseException $exception) {
                    $errors[$fieldPath] = ['invalid', $exception->getExpectedType(), $exception->getPassedType()];
                } catch (OptionsBuildException $exception) {
                    foreach ($exception->getFieldErrors() as $field => $error)
                        $errors[$field] = $error;
                }
            }
            if (empty($errors))
                $instance = Type::of($abstract)->getReflection()->newInstanceArgs($arguments);
        } else {
            $instance = Type::of($abstract)->getReflection()->newInstance();
        }

        $propertyValues = [];
        foreach ($nonStaticProperties as $property) {
            $name = $property->getName();
            $type = $property->getType();
            $fieldPath = $topField . '.' . $name;
            $listAttribute = $property->getAttributes(ListOf::class)[0] ?? null;
            $dictionaryAttribute = $property->getAttributes(DictionaryOf::class)[0] ?? null;
            $enumerableClass = null;
            $enumerableKey = null;
            if (!is_null($listAttribute)) {
                $listAttributeInstance = $listAttribute->newInstance();
                $enumerableClass = $listAttributeInstance->getClassName();
            }
            if (!is_null($dictionaryAttribute)) {
                $dictionaryAttributeInstance = $dictionaryAttribute->newInstance();
                $enumerableClass = $dictionaryAttributeInstance->getClassName();
                $enumerableKey = $dictionaryAttributeInstance->getKey();
            }
            $typeName = is_null($type) ? '' : $type->getName();
            if (!in_array($name, $usedProperties)) {
                try {
                    $value = static::getPropertyValue($typeName, $name, $properties, $enumerableClass, $enumerableKey, $fieldPath);
                    if (is_null($value)) {
                        if ($property->hasDefaultValue())
                            $value = $property->getDefaultValue();
                        elseif ($property->hasType() && $type->allowsNull())
                            $value = null;
                        else
                            $errors[$fieldPath] = ['null', $typeName];
                    }
                    $propertyValues[$name] = $value;
                } catch (OptionParseException $exception) {
                    $errors[$fieldPath] = ['invalid', $exception->getExpectedType(), $exception->getPassedType()];
                } catch (OptionsBuildException $exception) {
                    foreach ($exception->getFieldErrors() as $field => $error)
                        $errors[$field] = $error;
                }
            }
        }
        if (!empty($errors))
            throw new OptionsBuildException($errors);
        foreach ($propertyValues as $name => $value)
            $instance->{$name} = $value;
        return $instance;
    }

    /**
     * @template C
     * @param $fromObject
     * @param C $toClass
     * @return C
     */
    public static function cast($fromObject, string $toClass, array $additionalProps = [])
    {
        return is_null($fromObject) ? null : static::build($toClass, array_merge((array)$fromObject, $additionalProps));
    }

    protected static function getPropertyValue(string $type, string $name, array $properties, ?string $enumerableClass = null, ?string $enumerableKey = null, string $topField = '')
    {
        $value = $properties[$name] ?? $properties[strtolower($name)];
        $valueType = gettype($value);
        if (is_null($value))
            return null;
        switch ($type) {
            case 'int':
                if (!is_int($value))
                    throw new OptionParseException('int', $valueType);
                return $value;
            case 'string':
                if (!is_string($value))
                    throw new OptionParseException('string', $valueType);
                return $value;
            case 'bool':
                if (!is_bool($value) && !is_int($value))
                    throw new OptionParseException('bool', $valueType);
                return (bool)$value;
            case 'float':
                if (!is_float($value) && !is_int($value))
                    throw new OptionParseException('float', $valueType);
                return (float)$value;
            case 'array':
                if (!is_array($value))
                    throw new OptionParseException('array', $valueType);
                $result = [];
                if (!is_null($enumerableClass))
                    foreach ($value as $item) {
                        if (!is_null($item)) {
                            $item = (array)$item;
                            if (is_null($enumerableKey))
                                $result[] = static::build($enumerableClass, $item, $topField);
                            else
                                $result[$item[$enumerableKey]] = static::build($enumerableClass, $item, $topField);
                        }
                    }
                else
                    $result = $value;
                return $result;
            default:
                if (!is_array($value))
                    throw new OptionParseException($type, $valueType);
                return static::build($type, $value, $topField);
        }
    }
}