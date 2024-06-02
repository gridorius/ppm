<?php

namespace PPM\Commands\Contracts;

use Closure;

abstract class CommandBase implements ICommand
{
    public abstract function execute(array $argv);

    public static function getClosure(): Closure
    {
        return function ($argv) {
            $command = new static();
            $command->execute($argv);
        };
    }
}