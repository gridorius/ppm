<?php

namespace PPM;

use Builder\BuildManager;
use PPM\Commands\AddSource;
use PPM\Commands\Auth;
use PPM\Commands\Build;
use PPM\Commands\BuildSolution;
use PPM\Commands\DeleteSource;
use PPM\Commands\Help;
use PPM\Commands\BuildPackage;
use PPM\Commands\Install;
use PPM\Commands\PackageList;
use PPM\Commands\Restore;
use PPM\Commands\RunTests;
use PPM\Commands\SourceList;
use PPM\Commands\UploadPackage;
use Exception;
use Terminal\CommandRouting\CommandRouteCommand;
use Terminal\CommandRouting\CommandsRouter;

class Program
{
    public static function main(array $argv = []): void
    {
        define('WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        $commands = new CommandsRouter();
        $commands->registerCommand("build package [build_directory]", new BuildPackage());
        $commands->registerCommand("build solution [build_directory]", new BuildSolution());
        $commands->registerCommand("build - [build_directory] -", new Build());
        $commands->registerCommand("sources list", new SourceList());
        $commands->registerCommand("sources add <source>", new AddSource());
        $commands->registerCommand("sources delete <source>", new DeleteSource());
        $commands->registerCommand("packages upload <source> <name> <version>", new UploadPackage());
        $commands->registerCommand("packages list", new PackageList());
        $commands->registerCommand("auth <source> <login>", new Auth());
        $commands->registerCommand("restore [restore_directory]", new Restore());
        $commands->registerCommand("install", new Install());
        $commands->registerCommand("test <test_project_phar>", new RunTests());
        $commands->registerCommand("help", new Help());
        $commands->setNotFoundHandler((new CommandRouteCommand([], ''))->setHandler(new Help()));

        try {
            $commands->handle($argv);
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
            exit(1);
        }
    }
}