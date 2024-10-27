<?php

namespace Ppm\Core;

use Exception;
use Ppm\Core\Commands\Architecture\ArchitectureCommandsConfiguration;
use Ppm\Core\Commands\Build\BuilderCommandsConfiguration;
use Ppm\Core\Commands\InstallExtensionCommand;
use Ppm\Core\Commands\InstallPpmCommand;
use Ppm\Core\Commands\Packages\PackagesCommandsConfiguration;
use Ppm\Core\Commands\Sources\SourcesCommandsConfiguration;
use Ppm\Framework\CurrentAssembly;
use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Terminal\CommandRouting\CommandsRouter;
use Ppm\Packages\PackagesManager;

class Program
{
    public static function main(array $argv = []): void
    {
        define('WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        define('TMP_DIRECTORY', Path::assemblyCombine('tmp'));
        $router = new CommandsRouter();
        $router->setDescriptionHeader('ppm', '<command>');
        try {
            $router->registerCommand("install extension <package> <version>", new InstallExtensionCommand());
            $router->registerCommand("install", new InstallPpmCommand());
            $router->applyConfiguration(new BuilderCommandsConfiguration());
            $router->applyConfiguration(new SourcesCommandsConfiguration());
            $router->applyConfiguration(new PackagesCommandsConfiguration());
            $router->applyConfiguration(new ArchitectureCommandsConfiguration());

            $packageManager = new PackagesManager();
            static::registerDirectoryCommands($router, $packageManager->getExtensionsDirectory()->getPath(), 'ext');
            static::registerDirectoryCommands($router, getcwd(), 'app');

            $router->handle($argv);
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
            exit(1);
        }
    }

    private static function registerDirectoryCommands(CommandsRouter $router, string $directory, string $prefix): void
    {
        $assembly = CurrentAssembly::getAssembly();
        $assemblyCommands = $assembly->getDirectoryCommands($directory);
        foreach ($assemblyCommands as $pharPath => $commands) {
            foreach ($commands as $command => $parameters)
                $router->register($prefix . ' ' . $command,
                    function (array $params, array $options) use ($pharPath, $parameters, $assembly) {
                        $assembly->includeAndLoad($pharPath);
                        $handler = explode('::', $parameters['handler']);
                        call_user_func($handler, $parameters, $options);
                    })
                    ->addDefinedOptions($parameters['options'] ?? [])
                    ->setDescription($parameters['description'] ?? '');
        }
    }
}