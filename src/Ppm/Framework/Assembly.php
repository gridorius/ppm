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

    private MemoryStorage $storage;

    public function __construct()
    {
        $this->storage = new MemoryStorage();
    }

    public function registerAutoloader(): void
    {
        $types = $this->storage->getArray(static::TYPES_MEMORY_KEY);
        spl_autoload_register(function ($type) use ($types) {
            if ($types->has($type))
                require $types->get($type);
            else
                throw new Exception("Type {$type} not found");
        });
    }

    public function preloadTypes(): void
    {
        $loadedTypes = $this->storage->getArray(static::LOADED_TYPES_MEMORY_KEY);
        $types = $this->storage->getArray(static::TYPES_MEMORY_KEY);
        foreach ($types as $type => $path) {
            if ($loadedTypes->has($type)) continue;
            class_exists($type);
            $loadedTypes->set($type, true);
        }
    }

    public function includeScripts(): void
    {
        $included = $this->storage->getArray(static::INCLUDED_MEMORY_KEY);
        $includes = $this->storage->getArray(static::INCLUDES_MEMORY_KEY);
        foreach ($includes as $path) {
            if ($included->has($path)) continue;
            require $path;
            $included->set($path, true);
        }
    }

    public function registerAssembly(string $name, string $directory): void
    {
        $assemblies = $this->storage->getArray(static::ASSEMBLIES_MEMORY_KEY);
        if ($assemblies->has($name)) return;
        $path = "phar://{$name}";
        $assemblies->set($name, [
            'path' => $path,
            'directory' => $directory,
            'name' => $name
        ]);
        $manifest = include $path . '/manifest.php';
        $this->registerTypes($manifest['types']);
        $this->registerResources($manifest['resources']);
        $this->registerIncludes($manifest['includes']);
        $this->includeDepends($manifest['depends'], $directory);
    }

    public function registerTypes(array $types): void
    {
        $typesStorage = $this->storage->getArray(static::TYPES_MEMORY_KEY);
        foreach ($types as $type => $path) {
            $typesStorage->set($type, $path);
        }
    }

    public function registerIncludes(array $includes): void
    {
        $includesStorage = $this->storage->getArray(static::INCLUDES_MEMORY_KEY);
        foreach ($includes as $path)
            $includesStorage->add($path);
    }

    public function registerResources(array $resources): void
    {
        foreach ($resources as $name => $path)
            Resources::addResource($name, $path);
    }

    private function includeDepends(array $depends, string $directory): void
    {
        $assemblies = $this->storage->getArray(static::ASSEMBLIES_MEMORY_KEY);
        foreach ($depends as $name)
            if (!$assemblies->has($name))
                try {
                    $this->includePhar($directory . DIRECTORY_SEPARATOR . $name . '.phar');
                } catch (Exception $exception) {
                    echo $exception->getMessage();
                }
    }

    public function includePhar(string $path): void
    {
        if (!file_exists($path))
            throw new Exception("File {$path} not found");
        require $path;
    }

    public function includeAndLoad(string $path): void
    {
        $this->includePhar($path);
        $this->load();
    }

    public function entrypoint($entrypoint, $argv = []): void
    {
        try {
            $this->registerAutoloader();
            $this->load();
            $entrypoint($argv);
        } catch (Exception $exception) {
            throw new \Ppm\Framework\Exception($exception);
        }
    }

    public function load(): void
    {
        $this->preloadTypes();
        $this->includeScripts();
    }

    public function getDirectoryCommands(string $directory): array
    {
        $commands = [];
        $directory = new Directory($directory);
        foreach ($directory->glob('*.phar') as $path) {
            $manifest = $this->getManifestByPath($path);
            $commands[$path] = $manifest['commands'];
        }
        return $commands;
    }

    public function getManifestByPath(string $path): array
    {
        return include "phar://{$path}/manifest.php";
    }
}