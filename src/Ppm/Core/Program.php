<?php

namespace Ppm\Core;

use Exception;
use Ppm\Core\Commands\Architecture\ArchitectureCommandsConfiguration;
use Ppm\Core\Commands\Build\BuilderCommandsConfiguration;
use Ppm\Core\Commands\InstallCommand;
use Ppm\Core\Commands\Packages\PackagesCommandsConfiguration;
use Ppm\Core\Commands\Sources\SourcesCommandsConfiguration;
use Ppm\Framework\CurrentAssembly;
use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Terminal\CommandRouting\CommandsRouter;

class Program
{
    public static function main(array $argv = []): void
    {
        define('WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        define('TMP_DIRECTORY', Path::assemblyCombine('tmp'));
        $assembly = CurrentAssembly::getAssembly();
        $router = new CommandsRouter();
        $router->setDescriptionHeader('ppm', '<command>');
        try {
            $router->registerCommand("install", new InstallCommand());
            $router->applyConfiguration(new BuilderCommandsConfiguration());
            $router->applyConfiguration(new SourcesCommandsConfiguration());
            $router->applyConfiguration(new PackagesCommandsConfiguration());
            $router->applyConfiguration(new ArchitectureCommandsConfiguration());

            $assemblyCommands = $assembly->getDirectoryCommands(getcwd());
            foreach ($assemblyCommands as $pharPath => $commands) {
                foreach ($commands as $command => $parameters)
                    $router->register('app ' . $command,
                        function (array $params, array $options) use ($pharPath, $parameters, $assembly) {
                            $assembly->includeAndLoad($pharPath);
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
}