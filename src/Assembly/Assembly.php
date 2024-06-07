<?php

namespace Assembly;

class Assembly
{
    protected static $insntance;

    protected array $types = [];
    protected array $phars = [];
    protected array $includes = [];

    private function __construct()
    {
    }

    public static function getInstance(): Assembly
    {
        if (empty(static::$insntance))
            static::$insntance = new static();

        return static::$insntance;
    }

    public function hasPhar(string $name): bool
    {
        return key_exists($name, $this->phars);
    }

    public function getType(string $name): string
    {
        return $this->types[$name];
    }

    public function hasType(string $name): bool
    {
        return key_exists($name, $this->types);
    }

    public function registerAutoloader()
    {
        spl_autoload_register(function ($entity) {
            if ($this->hasType($entity))
                require $this->getType($entity);
        });
    }

    public function preloadTypes()
    {
        foreach ($this->types as $type => $path) {
            class_exists($type);
        }
    }

    public function includeScripts()
    {
        foreach ($this->includes as $path)
            require $path;
    }

    public function registerAssembly(string $name, string $directory)
    {
        if (key_exists($name, $this->phars)) return;
        $path = "phar://{$name}";
        $this->phars[$name] = $path;
        $manifest = include $path . '/manifest.php';
        $this->registerPharFiles($manifest);
        $this->includeDepends($manifest['depends'], $directory);
    }

    public function includePhar(string $path)
    {
        if (!file_exists($path))
            throw new \Exception("Dependency {$path} not found");
        require $path;
    }

    public function entrypoint($entrypoint, $argv = [])
    {
        $this->registerAutoloader();
        $this->preloadTypes();
        $this->includeScripts();
        $entrypoint($argv);
    }

    private function registerPharFiles(array $manifest)
    {
        foreach ($manifest['types'] as $type => $path)
            $this->types[$type] = $path;

        foreach ($manifest['resources'] as $name => $path) {
            Resources::addResource($name, $path);
        }

        foreach ($manifest['includes'] as $path)
            $this->includes[] = $path;
    }

    private function includeDepends(array $depends, string $directory)
    {
        foreach ($depends as $name)
            if (!$this->hasPhar($name))
                $this->includePhar($directory . DIRECTORY_SEPARATOR . $name . '.phar');
    }
}