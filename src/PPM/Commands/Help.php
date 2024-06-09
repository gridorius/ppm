<?php

namespace PPM\Commands;

use Assembly\Resources;
use Terminal\CommandRouting\CommandBase;
use Terminal\ShellStyleParser;

class Help extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        echo ShellStyleParser::style(Resources::get('resources/help.txt')->getContent());
    }
}