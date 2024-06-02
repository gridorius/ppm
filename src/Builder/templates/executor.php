<?php

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
                include $this->getType($entity);
            else
                throw new Exception("Type {$entity} not found");
        });
    }

    public function preloadTypes()
    {
        foreach ($this->types as $type => $path) {
            class_exists($type);
        }
    }

    public function includeScripts(){
        foreach ($this->includes as $path)
            require $path;
    }

    public function registerAssembly(string $name)
    {
        if(key_exists($name, $this->phars)) return;
        $path = 'phar://' . __DIR__ . DIRECTORY_SEPARATOR . $name . '.phar';
        $manifest = json_decode(file_get_contents($path . DIRECTORY_SEPARATOR . 'manifest.json'), true);
        $this->registerPhar($name, $path, $manifest);
    }

    public function registerRuntimeAssembly(string $path)
    {
        $pharPath = 'phar://' . $path;
        $manifest = json_decode(file_get_contents($pharPath . DIRECTORY_SEPARATOR . 'manifest.json'), true);
        $this->registerPhar($manifest['name'], $pharPath, $manifest, dirname($path));

        foreach ($manifest['types'] as $type => $localPath)
            class_exists($type);

        foreach ($manifest['includes'] as $localPath)
            require $path . DIRECTORY_SEPARATOR . $localPath;
    }

    public function registerPhar(string $name, string $path, array $manifest, string $pharDirectory = null)
    {
        $this->phars[$name] = [
            'path' => $path,
            'manifest' => $manifest
        ];

        foreach ($manifest['types'] as $type => $localPath)
            $this->types[$type] = $path . DIRECTORY_SEPARATOR . $localPath;

        foreach ($manifest['resources'] as $name => $localPath) {
            Resources::addResource($name, $path . DIRECTORY_SEPARATOR . $localPath);
        }

        foreach ($manifest['includes'] as $localPath)
            $this->includes[] = $path . DIRECTORY_SEPARATOR . $localPath;

        if (!is_null($pharDirectory)) {
            foreach ($manifest['depends'] as $name)
                if (!$this->hasPhar($name))
                    include $pharDirectory . DIRECTORY_SEPARATOR . $name . '.phar';
        } else {
            foreach ($manifest['depends'] as $name)
                if (!$this->hasPhar($name))
                    include __DIR__ . DIRECTORY_SEPARATOR . $name . '.phar';
        }
    }

    public static function path(string ...$pathPairs): string{
        return __DIR__.DIRECTORY_SEPARATOR.implode("/", $pathPairs);
    }
}

class Resources
{
    protected static array $resources = [];

    public static function addResource(string $name, string $path)
    {
        static::$resources[$name] = new Resource($name, $path);
    }

    public static function has(string $name): bool{
        return key_exists($name, static::$resources);
    }

    public static function get(string $name): Resource{
        return static::$resources[$name];
    }
}

class Resource
{
    protected string $name;
    protected string $path;

    public function __construct(string $name, string $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContent(): string
    {
        return file_get_contents($this->path);
    }

    public function include(){
        return include $this->path;
    }
}

include 'PROJECT_NAME.phar';

$assembly = Assembly::getInstance();
$assembly->registerAutoloader();
$assembly->preloadTypes();
$assembly->includeScripts();

ENTRYPOINT_CLASS::ENTRYPOINT_METHOD($argv);


