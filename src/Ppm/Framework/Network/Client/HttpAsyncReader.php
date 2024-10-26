<?php

namespace Ppm\Framework\Network\Client;

use Ppm\Framework\Stream\Async\AsyncStreamsReader;
use Ppm\Framework\Types\Collection;

class HttpAsyncReader extends AsyncStreamsReader
{
    /**
     * @var HttpRequest[] $requests
     */
    protected array $requests;
    protected int $timeout;
    protected $onProgress;

    public function __construct(array $requests, int $timeout = 5, callable $onProgress = null)
    {
        $this->requests = $requests;
        $this->timeout = $timeout;
        $this->onProgress = $onProgress;
        parent::__construct(array_map([$this, 'sendBlocks'], $requests));
    }

    public function waitAll(): static
    {
        while (!$this->isCompleted())
            $this->awaitContent(0, 3000);
        return $this;
    }

    public function isCompleted(): bool
    {
        foreach ($this->receivers as $key => $receiver)
            if (!$receiver->isCompleted()) return false;

        return true;
    }

    public function getResponsesCollection(): Collection
    {
        return new Collection(array_map(function (HttpResponseReceiver $receiver) {
            return $receiver->getResponse();
        }, $this->receivers));
    }

    protected function sendBlocks(HttpRequest $request): HttpResponseReceiver
    {
        $client = new HttpSocketClient($request, $this->timeout);
        $client->sendBlocks();
        return new HttpResponseReceiver($client, $request, $this->onProgress);
    }
}