<?php

namespace Ppm\Framework\System\Proc\Workers;

class CommandWorker extends ObjectTransitWorker
{
    public function init(): void
    {
        $this->onMessage(function (array $command, array $headers) {
            call_user_func_array($command, $headers['args']);
        });
    }
}