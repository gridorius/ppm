<?php

namespace Ppm\Framework;

use Exception;
use Phar;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Resources\Resources;
use Ppm\Framework\Storage\MemoryStorage;
use Ppm\Framework\Storage\StorageArray;
use Ppm\Framework\System\Proc\CommandLauncher;
use Ppm\Framework\System\Proc\LaunchedProcess;
use Ppm\Framework\System\Proc\PhpRuntimeCommandConfiguration;
use Throwable;

class Assembly
{
    private MemoryStorage $storage;
    private StorageArray $assemblies;
    private StorageArray $types;
    private StorageArray $includes;
    private StorageArray $loadedTypes;
    private StorageArray $included;
    private ?string $entrypointProject = null;

    public function __construct()
    {
        $this->storage = new MemoryStorage();
        $this->assemblies = $this->storage->getArray('Assembly');
        $this->types = $this->storage->getArray('Types');
        $this->includes = $this->storage->getArray('Includes');
        $this->loadedTypes = $this->storage->getArray('LoadedTypes');
        $this->included = $this->storage->getArray('Types');
    }

    public function registerAutoloader(): void
    {
        spl_autoload_register(function ($type) {
            try {
                if ($this->types->has($type))
                    require $this->types->get($type);
                else
                    throw new Exception("Type {$type} not found");
            } catch (Exception $e) {
                AssemblyExceptionFormatter::showExceptionEndExit($e);
            }
        });
    }

    public function preloadTypes(): void
    {
        foreach ($this->types as $type => $path) {
            if ($this->loadedTypes->has($type)) continue;
            class_exists($type);
            $this->loadedTypes->set($type, true);
        }
    }

    public function includeScripts(): void
    {
        foreach ($this->includes as $path) {
            if ($this->included->has($path)) continue;
            require $path;
            $this->included->set($path, true);
        }
    }

    public function registerAssembly(string $name, string $directory): void
    {
        if (is_null($this->entrypointProject))
            $this->entrypointProject = $name;
        if ($this->assemblies->has($name)) return;
        $path = "phar://{$name}";
        $this->assemblies->set($name, [
            'path' => $path,
            'realPath' => $directory . DIRECTORY_SEPARATOR . $name . '.phar',
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
        foreach ($types as $type => $path) {
            $this->types->set($type, $path);
        }
    }

    public function registerIncludes(array $includes): void
    {
        foreach ($includes as $path)
            $this->includes->add($path);
    }

    public function registerResources(array $resources): void
    {
        foreach ($resources as $name => $path)
            Resources::addResource($name, $path);
    }

    private function includeDepends(array $depends, string $directory): void
    {
        foreach ($depends as $name)
            if (!$this->assemblies->has($name))
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
            error_reporting(0);
            set_error_handler([AssemblyExceptionFormatter::class, 'formatError'], E_ERROR);
            register_shutdown_function(function () use ($argv) {
                $error = error_get_last();
                if ($error !== NULL)
                    AssemblyExceptionFormatter::formatError($error["type"], $error["message"], $error["file"], $error["line"]);
            });
            $this->registerAutoloader();
            $this->load();
            call_user_func($entrypoint, $argv);
        } catch (Throwable $exception) {
            AssemblyExceptionFormatter::showExceptionEndExit($exception);
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

    public static function getPath(): string
    {
        return Phar::running(false);
    }

    public function getEntrypointProject(): ?string
    {
        return $this->entrypointProject;
    }

    public function createCommandByProject(string $project, callable $entrypoint, ...$arguments): PhpRuntimeCommandConfiguration
    {
        return static::createCommandByPath($this->assemblies->get($project)['realPath'], $entrypoint, ...$arguments);
    }

    public function createCommandByPath(string $pathToProjectPhar, callable $entrypoint, ...$arguments): PhpRuntimeCommandConfiguration
    {
        if (!is_file($pathToProjectPhar))
            throw new Exception("File {$pathToProjectPhar} not found");

        $assemblyPath = static::getPath();
        $lines = [
            "require '{$assemblyPath}';",
            "require '{$pathToProjectPhar}';",
            CurrentAssembly::class . "::getAssembly()->entrypoint(",
            var_export($entrypoint, true),
            ',',
            '$argv ?? []',
            ");"
        ];

        return new PhpRuntimeCommandConfiguration(implode('', $lines), ...$arguments);
    }

    public function createCommand(callable $entrypoint, ...$arguments): PhpRuntimeCommandConfiguration
    {
        return static::createCommandByProject($this->entrypointProject, $entrypoint, ...$arguments);
    }
}