<?php

namespace Ppm\Core\Commands\Sources;

use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class AuthCommand extends CommandBase
{
    public function execute(array $parameters, array $options, array $argv): void
    {
        echo 'Password:';
        if (!WIN) {
            $f = popen("/bin/bash -c 'read -s password; echo \$password'", "r");
            $password = trim(fgets($f, 100));
            pclose($f);
        } else {
            $password = fgets(STDIN);
        }
        echo "\n";

        $packageController = new PackagesManager();
        $packageController->getSources()->authorize($parameters['source'], $parameters['login'], $password, $parameters['alias']);
    }

    public function getDescription(): string
    {
        return 'Auth to source';
    }
}