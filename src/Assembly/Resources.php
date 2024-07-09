<?php

namespace Assembly;

use Exception;

class Resources
{
    /**
     * @var array<string, Resource> $resources
     */
    private static array $resources = [];

    public static function addResource(string $name, string $path): void
    {
        static::$resources[$name] = new Resource($path);
    }

    public static function has(string $name): bool
    {
        return key_exists($name, static::$resources);
    }

    public static function get(string $name): Resource
    {
        if (!static::has($name))
            throw new Exception("Resource {$name} not found");

        return static::$resources[$name];
    }

    public static function find(string $pattern): array
    {
        $result = [];
        foreach (static::$resources as $name => $resource)
            if (fnmatch($pattern, $name))
                $result[$name] = $resource;

        return $result;
    }

    public static function findPaths(string $pattern): array
    {
        $result = [];
        foreach (static::$resources as $name => $resource)
            if (fnmatch($pattern, $name))
                $result[$name] = $resource->getPath();

        return $result;
    }
}