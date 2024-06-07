<?php

namespace PPM\Commands;

use Packages\PackagesController;
use PPM\Commands\Contracts\CommandBase;
use Exception;
use Packages\PackageManager;

class Auth extends CommandBase
{

    public function execute(array $argv)
    {
        if (empty($argv[0]))
            throw new Exception("Expected parameter source");
        if (empty($argv[1]))
            throw new Exception("Expected parameter login");
        $source = $argv[0];
        $login = $argv[1];

        echo 'Password:';
        $f = popen("/bin/bash -c 'read -s password; echo \$password'","r");
        $password = trim(fgets($f,100));
        pclose($f);
        echo "\n";

        $packageController = new PackagesController();
        $packageController->getSources()->authorize($source, $login, $password);
    }
}