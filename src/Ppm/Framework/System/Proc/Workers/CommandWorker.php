<?php

namespace Ppm\Framework\System\Proc\Workers;

use Ppm\Framework\Stream\Async\AsyncStreamWatcher;

class CommandWorker extends ObjectTransferWorker
{
    public function init(): void
    {
        $this->onMessage(function (array $command, array $headers) {
            call_user_func_array($command, $headers['args']);
        });

        AsyncStreamWatcher::single($this->getBind())->start();
    }
}