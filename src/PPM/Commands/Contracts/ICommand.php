<?php

namespace PPM\Commands\Contracts;

interface ICommand
{
    public function execute(array $argv);
}