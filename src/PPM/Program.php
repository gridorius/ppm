<?php

namespace PPM;

use Builder\BuildManager;
use PPM\Commands\AddSource;
use PPM\Commands\Auth;
use PPM\Commands\Build;
use PPM\Commands\DeleteSource;
use PPM\Commands\Help;
use PPM\Commands\BuildPackage;
use PPM\Commands\Restore;
use PPM\Commands\SourceList;
use PPM\Commands\UploadPackage;
use Exception;
use Terminal\CommandTree;

class Program
{
    public static function main(array $argv = [])
    {
        define('WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        $commands = new CommandTree();
        $commands->endPoint(Help::getClosure());
        $commands->awaitCommand('help')->endPoint(Help::getClosure());
        $commands->awaitCommand('build', function ($buildTree) {
            $buildTree->awaitCommand('package')->endPoint(BuildPackage::getClosure());
            $buildTree->endPoint(Build::getClosure());
        });
        $commands->awaitCommand('packages', function ($packages) {
            $packages->awaitCommand('upload')->endPoint(UploadPackage::getClosure());
        });
        $commands->awaitCommand('sources', function ($sources) {
            $sources->awaitCommand('add')->endPoint(AddSource::getClosure());
            $sources->awaitCommand('delete')->endPoint(DeleteSource::getClosure());
            $sources->awaitCommand('list')->endPoint(SourceList::getClosure());
        });

        $commands->awaitCommand('auth')->endPoint(Auth::getClosure());
        $commands->awaitCommand('restore')->endPoint(Restore::getClosure());

        try {
            $commands->step($argv);
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
            exit(1);
        }
    }
}