<?php

namespace PPM\Commands;

use Packages\PackagesController;
use Terminal\CommandRouting\CommandBase;

class Auth extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        echo 'Password:';
        $f = popen("/bin/bash -c 'read -s password; echo \$password'", "r");
        $password = trim(fgets($f, 100));
        pclose($f);
        echo "\n";

        $packageController = new PackagesController();
        $packageController->getSources()->authorize($parameters['source'], $parameters['login'], $password);
    }
}