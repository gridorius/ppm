<?php

namespace Ppm\Framework\Network\Client;

use Ppm\Framework\Stream\Async\Contracts\StreamReceiverBase;

class HttpResponseReceiver extends StreamReceiverBase
{
    private ResponseDataParser $parser;

    private HttpRequest $request;

    private $onProgress;

    public function __construct(HttpSocketClient $stream, HttpRequest $request, callable $onProgress = null)
    {
        parent::__construct($stream);
        $this->request = $request;
        $this->onProgress = $onProgress;
        $this->parser = new ResponseDataParser($stream, $onProgress);
    }

    public function onReadyContent(): void
    {
        $this->parser->handleInput();
        if (!empty($location = $this->parser->getResponse()->getHeader('Location'))) {
            $this->request->setUrl($location);
            $client = new HttpSocketClient($this->request, 5);
            $client->sendBlocks();
            $this->target = $client;
            $this->parser = new ResponseDataParser($client, $this->onProgress);
        }
    }

    public function isCompleted(): bool
    {
        return $this->parser->isCompleted();
    }

    public function getResponse(): HttpResponse
    {
        return $this->parser->getResponse();
    }
}