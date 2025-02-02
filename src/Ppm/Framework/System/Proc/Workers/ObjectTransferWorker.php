<?php

namespace Ppm\Framework\System\Proc\Workers;

use Ppm\Framework\System\Proc\Workers\Contracts\WorkerBase;

class ObjectTransferWorker extends WorkerBase
{
    public function sendData($data, array $headers = []): void
    {
        $headers = [
            '__type' => 'serialized',
            ...$headers
        ];
        parent::send(serialize($data), $headers);
    }

    public function init(array $arguments): void
    {

    }

    protected function callMessageHandlers(): void
    {
        if ($this->receiver->getHeaders()['__type'] == 'serialized') {
            $message = unserialize($this->receiver->getMessage());
            foreach ($this->handlers as $handler)
                call_user_func($handler, $this->receiver, $message);
        } else
            foreach ($this->handlers as $handler)
                call_user_func($handler, $this->receiver);
    }
}