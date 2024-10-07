<?php

namespace Ppm\Framework\Storage;

class MemoryStorage
{
    private static array $data = [];

    public static function setString(string $storageKey, string $value): void
    {
        static::$data[$storageKey] = $value;
    }

    public static function getString(string $storageKey): ?string
    {
        return (string)static::$data[$storageKey];
    }

    public static function setInt(string $storageKey, int $value): void
    {
        static::$data[$storageKey] = $value;
    }

    public static function getInt(string $storageKey): ?int
    {
        return (int)static::$data[$storageKey];
    }

    public static function setArray(string $storageKey, array $value): void
    {
        static::$data[$storageKey] = $value;
    }

    public static function getArray(string $storageKey): StorageArray
    {
        if (is_null(static::$data[$storageKey]))
            static::$data[$storageKey] = [];
        return new StorageArray(static::$data[$storageKey]);
    }

    public static function setObject(string $storageKey, object $value): void
    {
        static::$data[$storageKey] = $value;
    }

    public static function getObject(string $storageKey): ?object
    {
        return (object)static::$data[$storageKey];
    }


    public static function keyExists(string $storageKey): bool
    {
        return key_exists($storageKey, static::$data);
    }
}