<?php

namespace Ppm\Framework\System\Proc;

class PhpRuntimeCommandConfiguration extends CommandConfiguration
{
    public function getCommand(): array
    {
        $command = parent::getCommand();
        $code = array_shift($command);
        return ['php', '-r', $code, '--', ...$command];
    }
}