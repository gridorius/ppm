<?php

namespace PPM\Commands;

use PPM\Commands\Contracts\CommandBase;

class Help extends Contracts\CommandBase
{
    public function execute(array $argv)
    {
        echo <<<HELP
- build [out directory] [build directory] - build project
        build directory default current directory
        out directory default current directory/out
        
- build package [build directory] - build package
        build directory default current directory
        -p <source> - to auto upload created package
        
- auth <source> <login> - login to source

- sources [add|delete] <source> - add/delete source

- sources list - show list of sources

- restore - restore packages

- packages upload <source> [build directory] - upload package
        build directory default current directory

HELP;

    }
}