<?php

namespace Ppm\Framework\System\Proc\Workers;

class ObjectTransitWorkerCollection extends WorkerCollection
{
    public function sendData($data, array $headers = []): void
    {
        foreach ($this->workers as $worker)
            $worker->sendData($data, $headers);
    }
}