<?php

namespace Ppm\Framework;

use Exception;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Resources\Resources;
use Ppm\Framework\Storage\MemoryStorage;

class Assembly
{
    const ASSEMBLIES_MEMORY_KEY = 'Assembly';
    const TYPES_MEMORY_KEY = 'Types';
    const INCLUDES_MEMORY_KEY = 'Includes';
    const LOADED_TYPES_MEMORY_KEY = 'LoadedTypes';
    const INCLUDED_MEMORY_KEY = 'Included';

    public static function registerAutoloader(): void
    {
        $types = MemoryStorage::getArray(static::TYPES_MEMORY_KEY);
        spl_autoload_register(function ($entity) use ($types) {
            if ($types->has($entity))
                require $types->get($entity);
        });
    }

    public static function preloadTypes(): void
    {
        $loadedTypes = MemoryStorage::getArray(static::LOADED_TYPES_MEMORY_KEY);
        $types = MemoryStorage::getArray(static::TYPES_MEMORY_KEY);
        foreach ($types as $type => $path) {
            if ($loadedTypes->has($type)) continue;
            class_exists($type);
            $loadedTypes->set($type, true);
        }
    }

    public static function includeScripts(): void
    {
        $included = MemoryStorage::getArray(static::INCLUDED_MEMORY_KEY);
        $includes = MemoryStorage::getArray(static::INCLUDES_MEMORY_KEY);
        foreach ($includes as $path) {
            if ($included->has($path)) continue;
            require $path;
            $included->set($path, true);
        }
    }

    public static function registerAssembly(string $name, string $directory): void
    {
        $assemblies = MemoryStorage::getArray(static::ASSEMBLIES_MEMORY_KEY);
        if ($assemblies->has($name)) return;
        $path = "phar://{$name}";
        $assemblies->set($name, [
            'path' => $path,
            'directory' => $directory,
            'name' => $name
        ]);
        $manifest = include $path . '/manifest.php';
        static::registerTypes($manifest['types']);
        static::registerResources($manifest['resources']);
        static::registerIncludes($manifest['includes']);
        static::includeDepends($manifest['depends'], $directory);
    }

    public static function registerTypes(array $types): void
    {
        $types = MemoryStorage::getArray(static::TYPES_MEMORY_KEY);
        foreach ($types as $type => $path)
            $types->set($type, $path);
    }

    public static function registerIncludes(array $includes): void
    {
        $includes = MemoryStorage::getArray(static::INCLUDES_MEMORY_KEY);
        foreach ($includes as $path)
            $includes->add($path);
    }

    public static function registerResources(array $resources): void
    {
        foreach ($resources as $name => $path)
            Resources::addResource($name, $path);
    }

    private static function includeDepends(array $depends, string $directory): void
    {
        $assemblies = MemoryStorage::getArray(static::ASSEMBLIES_MEMORY_KEY);
        foreach ($depends as $name)
            if (!$assemblies->has($name))
                try {
                    static::includePhar($directory . DIRECTORY_SEPARATOR . $name . '.phar');
                } catch (Exception $exception) {
                    echo $exception->getMessage();
                }
    }

    public static function includePhar(string $path): void
    {
        if (!file_exists($path))
            throw new Exception("File {$path} not found");
        require $path;
    }

    public static function entrypoint($entrypoint, $argv = []): void
    {
        try {
            static::registerAutoloader();
            static::preload();
            $entrypoint($argv);
        } catch (Exception $exception) {
            throw new \Ppm\Framework\Exception($exception);
        }
    }

    public static function preload(): void
    {
        static::preloadTypes();
        static::includeScripts();
    }

    public static function getDirectoryCommands(string $directory): array
    {
        $commands = [];
        $directory = new Directory($directory);
        foreach ($directory->glob('*.phar') as $path) {
            $manifest = static::getManifestByPath($path);
            $commands[$path] = $manifest['commands'];
        }
        return $commands;
    }

    public static function getManifestByPath(string $path): array
    {
        return include "phar://{$path}/manifest.php";
    }
}