<?php

namespace Ppm\Framework\System\Proc\Workers;

class ObjectTransitWorker extends WorkerBase
{
    public function sendData($data, array $headers = []): void
    {
        $headers = [
            'type' => 'serialized',
            ...$headers
        ];
        parent::send(serialize($data), $headers);
    }

    public function init(): void
    {

    }

    protected function callMessageHandlers(): void
    {
        if ($this->protocol->getHeaders()['type'] == 'serialized') {
            $message = unserialize($this->protocol->getMessage());
            foreach ($this->handlers as $handler)
                call_user_func($handler, $message, $this->protocol->getHeaders());
        } else
            foreach ($this->handlers as $handler)
                call_user_func($handler, $this->protocol->getMessage(), $this->protocol->getHeaders());
    }
}