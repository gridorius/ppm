<?php

namespace Assembly;

use Exception;

class Assembly
{
    private static array $types = [];
    private static array $phars = [];
    private static array $includes = [];

    private static array $loadedTypes = [];
    private static array $includedScripts = [];

    public static function hasPhar(string $name): bool
    {
        return key_exists($name, static::$phars);
    }

    public static function getTypePath(string $name): string
    {
        return static::$types[$name];
    }

    public static function hasType(string $name): bool
    {
        return key_exists($name, static::$types);
    }

    public static function registerAutoloader(): void
    {
        spl_autoload_register(function ($entity) {
            if (static::hasType($entity))
                require static::getTypePath($entity);
        });
    }

    public static function preloadTypes(): void
    {
        foreach (static::$types as $type => $path) {
            if (key_exists($type, static::$loadedTypes)) continue;
            class_exists($type);
            static::$loadedTypes[$type] = true;
        }
    }

    public static function includeScripts(): void
    {
        foreach (static::$includes as $path) {
            if (key_exists($path, static::$includedScripts)) continue;
            require $path;
            static::$includedScripts[$path] = true;
        }
    }

    public static function registerAssembly(string $name, string $directory): void
    {
        if (static::hasPhar($name)) return;
        $path = "phar://{$name}";
        static::$phars[$name] = $path;
        $manifest = include $path . '/manifest.php';
        static::registerPharFiles($manifest);
        static::includeDepends($manifest['depends'], $directory);
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
            static::preloadTypes();
            static::includeScripts();
            $entrypoint($argv);
        } catch (Exception $exception) {
            throw new \Assembly\Exception($exception);
        }
    }

    private static function registerPharFiles(array $manifest): void
    {
        foreach ($manifest['types'] as $type => $path)
            static::$types[$type] = $path;

        foreach ($manifest['resources'] as $name => $path)
            Resources::addResource($name, $path);

        foreach ($manifest['includes'] as $path)
            static::$includes[] = $path;
    }

    private static function includeDepends(array $depends, string $directory): void
    {
        foreach ($depends as $name)
            if (!static::hasPhar($name))
                static::includePhar($directory . DIRECTORY_SEPARATOR . $name . '.phar');
    }
}