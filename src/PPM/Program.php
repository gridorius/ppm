<?php

namespace PPM;

use Builder\BuildManager;
use PPM\Commands\AddSource;
use PPM\Commands\Auth;
use PPM\Commands\Build;
use PPM\Commands\BuildSolution;
use PPM\Commands\DeleteSource;
use PPM\Commands\DownloadPackage;
use PPM\Commands\ExtractPackage;
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
        try {
            $commands->registerCommand("build package - [build_directory]", new BuildPackage())
                ->setDefinedOptions([
                    'r' => false
                ]);
            $commands->registerCommand("build solution [build_directory]", new BuildSolution());
            $commands->registerCommand("build - [build_directory] -", new Build())
                ->setDefinedOptions([
                    'o' => true
                ]);
            $commands->registerCommand("sources list", new SourceList());
            $commands->registerCommand("sources add <source> [alias]", new AddSource());
            $commands->registerCommand("sources delete <source>", new DeleteSource());
            $commands->registerCommand("packages download <name> <version>", new DownloadPackage());
            $commands->registerCommand("packages upload <source> <name> <version>", new UploadPackage());
            $commands->registerCommand("packages extract <name> <version> [out_directory]", new ExtractPackage());
            $commands->registerCommand("packages list", new PackageList());
            $commands->registerCommand("auth <source> <login> [alias]", new Auth());
            $commands->registerCommand("restore [restore_directory]", new Restore());
            $commands->registerCommand("install", new Install());
            $commands->registerCommand("test <test_project_phar>", new RunTests());

            $commands->handle($argv);
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
            exit(1);
        }
    }
}