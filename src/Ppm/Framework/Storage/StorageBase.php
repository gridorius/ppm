<?php

namespace Ppm\Framework\Storage;

use Ppm\Framework\Traits\Observable;

abstract class StorageBase
{
    protected array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function setString(string $storageKey, string $value): void
    {
        $this->data[$storageKey] = $value;
        $this->onUpdateValue($storageKey, $value);
    }

    public function getString(string $storageKey): ?string
    {
        return (string)$this->data[$storageKey];
    }

    public function setInt(string $storageKey, int $value): void
    {
        $this->data[$storageKey] = $value;
        $this->onUpdateValue($storageKey, $value);
    }

    public function getInt(string $storageKey): ?int
    {
        return (int)$this->data[$storageKey];
    }

    public function setArray(string $storageKey, array $value): void
    {
        $this->data[$storageKey] = $value;
        $this->onUpdateValue($storageKey, $value);
    }

    public function getArray(string $storageKey): StorageArray
    {
        if (!self::keyExists($storageKey))
            $this->data[$storageKey] = [];
        $storageArray = new StorageArray($this->data[$storageKey]);
        $storageArray->onUpdate(function () use ($storageKey) {
            $this->onUpdateValue($storageKey, $this->data[$storageKey]);
        });
        return $storageArray;
    }

    public function setObject(string $storageKey, object $value): void
    {
        $this->data[$storageKey] = $value;
        $this->onUpdateValue($storageKey, $value);
    }

    public function getObject(string $storageKey): ?object
    {
        return (object)$this->data[$storageKey];
    }

    public function keyExists(string $storageKey): bool
    {
        return key_exists($storageKey, $this->data);
    }

    abstract protected function onUpdateValue(string $key, $value): void;
}