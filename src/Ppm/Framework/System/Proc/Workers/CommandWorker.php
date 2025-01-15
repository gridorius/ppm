<?php

namespace Ppm\Framework\System\Proc\Workers;

use Ppm\Framework\Stream\Async\AsyncStreamWatcher;
use Ppm\Framework\Stream\MessageProtocol\MessageReceiver;

class CommandWorker extends ObjectTransferWorker
{
    public function init(array $arguments): void
    {
        $this->onMessage(function (MessageReceiver $protocol, array $command) {
            call_user_func_array($command, $protocol->getHeaders()['args']);
        });

        AsyncStreamWatcher::single($this->getBind())->start();
    }
}