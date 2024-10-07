<?php

namespace Ppm\Framework\Network\Client;

use Ppm\Framework\Network\RequestMethods;
use Ppm\Framework\Network\Socket\SocketClient;
use Ppm\Framework\Stream\Parallel\ParallelStreamsReader;

class HttpClient
{
    protected int $blockSize = 8192;

    public function get(string $url): RequestData
    {
        return new RequestData($url);
    }

    public function post(string $url): RequestData
    {
        return new RequestData($url, RequestMethods::POST);
    }

    public function put(string $url): RequestData
    {
        return new RequestData($url, RequestMethods::PUT);
    }

    public function patch(string $url): RequestData
    {
        return new RequestData($url, RequestMethods::PATCH);
    }

    public function delete(string $url): RequestData
    {
        return new RequestData($url, RequestMethods::DELETE);
    }

    public function head(string $url): RequestData
    {
        return new RequestData($url, RequestMethods::HEAD);
    }

    public function options(string $url): RequestData
    {
        return new RequestData($url, RequestMethods::OPTIONS);
    }

    public function send(RequestData $request, int $timeout = 5): Response
    {
        $client = $this->prepareClient($request, $timeout);
        $client->sendBlocks();
        return $client->waitResponse();
    }

    public function prepareClient(RequestData $request, int $timeout = 5): HttpSocketClient
    {
        return new HttpSocketClient($request, $timeout);
    }

    public function sendParallel(array $requests, int $timeout = 5): ParallelStreamsReader
    {
        $receivers = [];
        foreach ($requests as $request) {
            $client = $this->prepareClient($request, $timeout);
            $receivers[] = new HttpResponseReceiver($client);
            $client->sendBlocks();
        }

        return new ParallelStreamsReader($receivers);
    }

    public function createSocketClient(RequestData $request, int $timeout): SocketClient
    {
        $parsedRequest = parse_url($request->getUrl());
        $port = $parsedRequest["port"] ?? ($parsedRequest["scheme"] == 'http' ? 80 : 443);
        return new SocketClient('tcp://' . $parsedRequest['host'] . ':' . $port, $timeout);
    }
}