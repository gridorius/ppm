<?php

namespace Ppm\Framework\Network\Client;

use Ppm\Framework\Network\Socket\SocketHostPortClient;

class HttpSocketClient extends SocketHostPortClient
{
    private HttpRequest $request;

    private int $timeout;

    /**
     * @param HttpRequest $request
     */
    public function __construct(HttpRequest $request, int $timeout = 5)
    {
        $this->request = $request;
        $this->timeout = $timeout;
        $urlData = $request->getUrlData();
        $port = $urlData["port"] ?? 80;
        parent::__construct($urlData['host'], $port, $timeout);
        $this->unblock();
    }

    public function sendBlocks(callable $onProgress = null, int $blockSize = 8192): static
    {
        $data = RawRequestBuilder::build($this->request);
        $length = strlen($data);
        $uploadedLength = 0;
        $blocks = str_split($data, $blockSize);
        foreach ($blocks as $block) {
            $this->write($block);
            $uploadedLength += strlen($block);
            if ($onProgress !== null)
                call_user_func($onProgress, $length, $uploadedLength);
        }

        return $this;
    }

    public function waitResponse(callable $onProgress = null): HttpResponse
    {
        $parallel = new HttpAsyncReader([$this->request], $this->timeout, $onProgress);
        return $parallel->waitAll()->getResponsesCollection()->first();
    }
}