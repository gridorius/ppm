<?php

namespace Ppm\Framework\Network\Client;

use Ppm\Framework\Network\Socket\SocketClient;
use Ppm\Framework\Stream\Parallel\ParallelStreamsReader;

class HttpSocketClient extends SocketClient
{
    private RequestData $request;

    /**
     * @param RequestData $request
     */
    public function __construct(RequestData $request, int $timeout = 5)
    {
        $this->request = $request;
        $parsedRequest = parse_url($request->getUrl());
        $port = $parsedRequest["port"] ?? ($parsedRequest["scheme"] == 'http' ? 80 : 443);
        parent::__construct('tcp://' . $parsedRequest['host'] . ':' . $port, $timeout);
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
    }

    public function waitResponse(callable $onProgress = null): Response
    {
        $receiver = new HttpResponseReceiver($this, $onProgress);
        $parallel = new ParallelStreamsReader([$receiver]);
        $parallel->handleInput(0, 30000);
        return $receiver->getResponse();
    }
}