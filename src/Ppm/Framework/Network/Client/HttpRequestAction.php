<?php

namespace Ppm\Framework\Network\Client;

class HttpRequestAction extends HttpRequest
{
    public function send(int $timeout = 5): HttpResponse
    {
        return $this->sendBlocks($timeout)->waitResponse();
    }

    public function sendBlocks(int $timeout = 5, callable $onProgress = null): HttpSocketClient
    {
        $client = new HttpSocketClient($this, $timeout);
        return $client->sendBlocks($onProgress);
    }
}