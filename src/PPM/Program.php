<?php

namespace PPM;

use Assembly\Assembly;
use Assembly\Utils;
use Builder\Configuration\ConfigurationCollector;
use Packages\PackagesController;
use PPM\Commands\AddSource;
use PPM\Commands\Auth;
use PPM\Commands\Build;
use PPM\Commands\BuildSolution;
use PPM\Commands\CompactPackage;
use PPM\Commands\DeleteSource;
use PPM\Commands\DownloadPackage;
use PPM\Commands\ExtractPackage;
use PPM\Commands\BuildPackage;
use PPM\Commands\Install;
use PPM\Commands\PackageList;
use PPM\Commands\Restore;
use PPM\Commands\Run;
use PPM\Commands\SourceList;
use PPM\Commands\UploadPackage;
use Exception;
use Terminal\CommandRouting\CommandsRouter;
use Utils\PathUtils;

class Program
{
    public static function main(array $argv = []): void
    {
        define('WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        define('TMP_DIRECTORY', Utils::path('tmp'));

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
            $commands->registerCommand("run [build_directory]", new Run());
            $commands->registerCommand("sources list", new SourceList());
            $commands->registerCommand("sources add <source> [alias]", new AddSource());
            $commands->registerCommand("sources delete <source>", new DeleteSource());
            $commands->registerCommand("packages compact <name> <version>", new CompactPackage());
            $commands->registerCommand("packages download <name> <version>", new DownloadPackage());
            $commands->registerCommand("packages upload <source> <name> <version>", new UploadPackage());
            $commands->registerCommand("packages extract <name> <version> [out_directory]", new ExtractPackage());
            $commands->registerCommand("packages list", new PackageList());
            $commands->registerCommand("auth <source> <login> [alias]", new Auth());
            $commands->registerCommand("restore [restore_directory]", new Restore());
            $commands->registerCommand("install", new Install());
//            $commands->registerCommand("test <test_project_phar>", new RunTests());

            if ($projectFile = PathUtils::findProj(getcwd()))
                static::includeProjectCommands($projectFile, $commands);

            $commands->handle($argv);
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
            exit(1);
        }
    }

    private static function includeProjectCommands(string $projectFile, CommandsRouter $commands): void
    {
        $packageController = new PackagesController();
        $localManager = $packageController->getLocalManager();
        $configurations = (new ConfigurationCollector())->collectFromProjectFile($projectFile);
        foreach ($configurations->getDepends() as $package => $version)
            if ($localPackage = $localManager->get($package, $version)) {
                Assembly::includePhar($localPackage->getProjectPharPath());
                foreach ($localPackage->getMetadata()['commands'] as $pattern => $parameters) {
                    $handler = explode('::', $parameters['handler']);
                    $commands
                        ->register('run ' . $pattern, function (array $arguments, array $options) use ($handler) {
                            call_user_func($handler, $arguments, $options);
                        })
                        ->setDescription($parameters['description'] ?? '')
                        ->setDefinedOptions($parameters['definedOptions'] ?? []);
                }
            }
        Assembly::preloadTypes();
        Assembly::includeScripts();
    }
}