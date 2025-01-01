<?php

namespace Ppm\Framework\Network\Client;

use Closure;
use Ppm\Framework\Stream\Async\AsyncStreamsReader;
use Ppm\Framework\Stream\Async\IBindable;
use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\Contracts\IStream;

class HttpRequestAction implements IBindable
{
    private HttpSocketClient $client;
    private ?Closure $uploadProgressHandler;
    private ?Closure $downloadProgressHandler;
    private HttpRequest $request;
    private ResponseDataParser $receiver;

    public function __construct(HttpRequest $request, callable $uploadProgressHandler = null)
    {
        $this->request = $request;
        $this->uploadProgressHandler = is_null($uploadProgressHandler) ? null : Closure::fromCallable($uploadProgressHandler);
    }

    public static function from(HttpRequest $request): static
    {
        return new static($request);
    }

    public function getClient(): ?HttpSocketClient
    {
        return $this->client;
    }

    public function send(callable $uploadProgressHandler = null): static
    {
        $this->client = $client = new HttpSocketClient($this->request);
        if (!is_null($uploadProgressHandler))
            $this->uploadProgressHandler = Closure::fromCallable($uploadProgressHandler);
        $client->send($this->uploadProgressHandler);
        return $this;
    }

    public function follow(string $location): void
    {
        $this->request->setUrl($location);
        $this->send();
    }

    public function waitResponse(callable $downloadProgressHandler = null, bool $followLocation = true): HttpResponse
    {
        $this->downloadProgressHandler = $downloadProgressHandler;
        $this->receiver = new ResponseDataParser($downloadProgressHandler);
        $parallel = new AsyncStreamsReader([$this->bind($this->client)]);
        while (!$this->receiver->isCompleted()) {
            $parallel->watch();
        }
        return $this->receiver->getResponse();
    }

    public function creteBind(callable $downloadProgressHandler = null): StreamReadActionBind
    {
        $this->downloadProgressHandler = $downloadProgressHandler;
        $this->receiver = new ResponseDataParser($downloadProgressHandler);
        return $this->bind($this->client);
    }

    public function getResponse(): HttpResponse
    {
        return $this->receiver->getResponse();
    }

    public function bind(IStream $stream): StreamReadActionBind
    {
        return StreamReadActionBind::create($stream, [
            $this,
            'onReadyContent'
        ]);
    }

    public function onReadyContent(IStream $stream): void
    {
        $this->receiver->onReadyContent($stream);
        if ($this->receiver->isCompleted() && !empty($location = $this->receiver->getResponse()->getHeader('Location'))) {
            $this->follow($location);
            $this->receiver = new ResponseDataParser($this->downloadProgressHandler);
        }
    }

    public function isCompleted(): bool
    {
        return $this->receiver->isCompleted();
    }
}