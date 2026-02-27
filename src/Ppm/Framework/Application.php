<?php

namespace Ppm\Framework;

use Exception;
use Phar;
use Ppm\Builder\Constants;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Resources\Resources;
use Ppm\Framework\System\Proc\PhpRuntimeCommandConfiguration;
use Ppm\Framework\Utils\StringUtils;
use Throwable;

class Application
{
    private static ?string $entrypointProject = null;

    /**
     * @var Assembly[]
     */
    private static array $assemblies = [];

    public static function getCurrentAssembly(?string $type = null): ?Assembly
    {
        if (is_null($type)) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $type = $trace[1]['class'];
        }

        foreach (static::$assemblies as $assembly)
            if ($assembly->hasType($type))
                return $assembly;

        return null;
    }

    public static function getAssemblies(): array
    {
        return static::$assemblies;
    }

    public static function registerAutoloader(): void
    {
        spl_autoload_register(function ($type) {
            foreach (static::$assemblies as $assembly)
                if ($assembly->hasType($type))
                    require $assembly->getTypePath($type);
        });
    }

    public static function registerAssembly(string $name, string $directory): void
    {
        if (key_exists($name, static::$assemblies)) return;
        if (is_null(static::$entrypointProject))
            static::$entrypointProject = $name;
        $assembly = new Assembly($name, $directory);
        static::$assemblies[$name] = $assembly;
        static::includeDependencies($assembly->getDepends(), $directory);
    }

    public static function includeProjectLibrary(string $name): void
    {
        $path = Path::assemblyCombine($name . '.phar');
        if (!file_exists($path))
            throw new Exception('File "' . $path . '" does not exist.');

        require $path;
    }

    public static function includePhar(string $path): void
    {
        if (!file_exists($path))
            throw new Exception("File {$path} not found");
        require $path;
    }

    public static function includeAndLoad(string $path): void
    {
        static::includePhar($path);
        static::load();
    }

    public static function entrypoint($entrypoint, $argv = []): void
    {
        try {
            error_reporting(0);
            set_error_handler([AssemblyExceptionFormatter::class, 'formatError'], E_ERROR);
            register_shutdown_function(function () use ($argv) {
                $error = error_get_last();
                if ($error !== NULL)
                    AssemblyExceptionFormatter::formatError($error["type"], $error["message"], $error["file"], $error["line"]);
            });
            static::registerAutoloader();
            static::load();
            call_user_func($entrypoint, $argv);
        } catch (Throwable $exception) {
            AssemblyExceptionFormatter::showExceptionEndExit($exception);
        }
    }

    public static function load(): void
    {
        foreach (static::$assemblies as $assembly) {
            if (!$assembly->isLoaded())
                $assembly->preloadTypes();
        }
        foreach (static::$assemblies as $assembly) {
            if (!$assembly->isLoaded())
                $assembly->registerResources();
        }
        foreach (static::$assemblies as $assembly) {
            if (!$assembly->isLoaded()) {
                $assembly->includeScripts();
                $assembly->markAssLoaded();
            }
        }
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

    public static function getPath(): string
    {
        return Phar::running(false);
    }

    public static function getEntrypointProject(): ?string
    {
        return static::$entrypointProject;
    }

    public static function createCommandByProject(string $project, $entrypoint, ...$arguments): PhpRuntimeCommandConfiguration
    {
        return static::createCommandByPath(static::$assemblies[$project]['realPath'], $entrypoint, ...$arguments);
    }

    public static function createCommandByPath(string $pathToProjectPhar, $entrypoint, ...$arguments): PhpRuntimeCommandConfiguration
    {
        if (!is_file($pathToProjectPhar))
            throw new Exception("File {$pathToProjectPhar} not found");

        $pathInfo = pathinfo($pathToProjectPhar);
        chdir($pathInfo['dirname']);
        $runnerContent = StringUtils::replace(
            Resources::get(Constants::RUNNER_TEMPLATE_PATH)->getContent(),
            [
                Constants::REPLACE_PROJECT_NAME => $pathInfo['filename'],
                Constants::REPLACE_ENTRYPOINT_DATA => var_export($entrypoint, true),
            ]
        );

        $runnerContent = preg_replace(["/\<\?php/", "/\n/", "/\r/"], '', $runnerContent);
        return new PhpRuntimeCommandConfiguration($runnerContent, ...$arguments);
    }

    public static function createCommand($entrypoint, ...$arguments): PhpRuntimeCommandConfiguration
    {
        return static::createCommandByProject(static::$entrypointProject, $entrypoint, ...$arguments);
    }

    private static function includeDependencies(array $dependencies, string $directory): void
    {
        foreach ($dependencies as $name)
            if (!key_exists($name, static::$assemblies))
                try {
                    static::includePhar($directory . DIRECTORY_SEPARATOR . $name . '.phar');
                } catch (Exception $exception) {
                    echo $exception->getMessage();
                }
    }
}