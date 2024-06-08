<?php

namespace PPM\Commands;

use Assembly\Resources;
use Terminal\ShellStyleParser;

class Help extends Contracts\CommandBase
{
    public function execute(array $argv)
    {
        echo ShellStyleParser::style(Resources::get('resources/help.txt')->getContent());
    }
}