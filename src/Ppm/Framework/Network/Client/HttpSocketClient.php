<?php

namespace Ppm\Framework\Network\Client;

use Ppm\Framework\Network\Socket\SocketHostPortClient;

class HttpSocketClient extends SocketHostPortClient
{
    private HttpRequest $request;

    /**
     * @param HttpRequest $request
     */
    public function __construct(HttpRequest $request, int $timeout = 5)
    {
        $this->request = $request;
        $urlData = $request->getUrlData();
        $port = $urlData["port"] ?? 80;
        parent::__construct($urlData['host'], $port, $timeout);
        $this->unblock();
    }

    public function getRequest(): HttpRequest
    {
        return $this->request;
    }

    public function send(?callable $onProgress = null): static
    {
        $data = RawRequestBuilder::build($this->request);
        $length = strlen($data);
        $uploadedLength = 0;
        while ($uploadedLength < $length) {
            $uploadedLength += $this->write(substr($data, $uploadedLength));
            if ($onProgress !== null)
                call_user_func($onProgress, $length, $uploadedLength);
        }

        return $this;
    }
}