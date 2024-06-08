<?php

namespace PPM\Commands;

use Assembly\Resources;

class Help extends Contracts\CommandBase
{
    public function execute(array $argv)
    {
        echo Resources::get('resources/help.txt')->getContent();
    }
}