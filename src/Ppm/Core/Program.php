<?php

namespace Ppm\Core;

use Exception;
use Ppm\Core\Commands\Architecture\ArchitectureCommandsConfiguration;
use Ppm\Core\Commands\Build\BuilderCommandsConfiguration;
use Ppm\Core\Commands\InstallCommand;
use Ppm\Core\Commands\Packages\PackagesCommandsConfiguration;
use Ppm\Core\Commands\Sources\SourcesCommandsConfiguration;
use Ppm\Framework\Assembly;
use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Terminal\CommandRouting\CommandsRouter;

class Program
{
    public static function main(array $argv = []): void
    {
        define('WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        define('TMP_DIRECTORY', Path::assemblyCombine('tmp'));
        $router = new CommandsRouter();
        $router->setDescriptionHeader('ppm', '<command>');
        try {
            $router->registerCommand("install", new InstallCommand());

            $router->applyConfiguration(new BuilderCommandsConfiguration());
            $router->applyConfiguration(new SourcesCommandsConfiguration());
            $router->applyConfiguration(new PackagesCommandsConfiguration());
            $router->applyConfiguration(new ArchitectureCommandsConfiguration());

            $assemblyCommands = Assembly::getDirectoryCommands(getcwd());
            foreach ($assemblyCommands as $pharPath => $commands) {
                foreach ($commands as $command => $parameters)
                    $router->register('run ' . $command, function (array $params, array $options) use ($pharPath, $parameters) {
                        Assembly::includePhar($pharPath);
                        Assembly::preload();
                        $handler = explode('::', $parameters['handler']);
                        call_user_func($handler, $parameters, $options);
                    })->addDefinedOptions($parameters['options'])
                        ->setDescription($parameters['description']);
            }

            $router->handle($argv);
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
            exit(1);
        }
    }

    private static function includeSolutionCommands(CommandsRouter $commands): void
    {
        foreach ($commands as $pharPath => $pharCommands) {
            Assembly::includePhar($pharPath);
            foreach ($pharCommands as $pattern => $params) {
                $commands
                    ->register('run ' . $pattern, function (array $arguments, array $options) use ($params) {
                        call_user_func($params['handler'], $arguments, $options);
                    })
                    ->setDescription($params['description'] ?? '')
                    ->addDefinedOptions($params['definedOptions'] ?? []);
            }
        }

        Assembly::preloadTypes();
        Assembly::includeScripts();
    }
}