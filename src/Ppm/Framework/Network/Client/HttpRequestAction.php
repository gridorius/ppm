<?php

namespace Ppm\Framework\Network\Client;

use Closure;
use Ppm\Framework\Network\ClientDisconnectedException;
use Ppm\Framework\Stream\Async\AsyncStreamWatcher;
use Ppm\Framework\Stream\Async\MainCycle;
use Ppm\Framework\Stream\Async\Promise;
use Ppm\Framework\Stream\Contracts\IStream;

class HttpRequestAction
{
    private HttpSocketClient $client;
    private ?Closure $uploadProgressHandler;
    private HttpRequest $request;
    private ResponseDataParser $receiver;
    private bool $followLocation;

    public function __construct(HttpRequest $request, ?callable $uploadProgressHandler = null)
    {
        $this->request = $request;
        $this->uploadProgressHandler = is_null($uploadProgressHandler) ? null : Closure::fromCallable($uploadProgressHandler);
        $this->followLocation = true;
    }

    public static function from(HttpRequest $request): static
    {
        return new static($request);
    }

    public function send(?callable $uploadProgressHandler = null): static
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

    public function wait(?callable $downloadProgressHandler = null, bool $followLocation = true): HttpResponse
    {
        $this->receiver = new ResponseDataParser($downloadProgressHandler);
        $this->followLocation = $followLocation;
        $watcher = new AsyncStreamWatcher();
        $watcher->addCoroutine((function () {
            yield $this->client;
            while (!$this->isCompleted())
                yield $this->onReadyContent($this->client);
        })());
        while (!$this->isCompleted())
            $watcher->watch();
        return $this->getResponse();
    }

    public function waitAsync(?callable $downloadProgressHandler = null): Promise
    {
        $this->receiver = new ResponseDataParser($downloadProgressHandler);

        return new Promise(function ($resolve) {
            MainCycle::watch((function () use ($resolve) {
                yield $this->client;
                while (!$this->isCompleted()) {
                    $this->onReadyContent($this->client);
                    if ($this->isCompleted())
                        $resolve($this->receiver->getResponse());
                    yield $this->client;
                }
            })());
        });
    }

    public function getResponse(): HttpResponse
    {
        return $this->receiver->getResponse();
    }

    public function onReadyContent(IStream $stream): HttpSocketClient
    {
        try {
            $this->receiver->onReadyContent($stream);
        } catch (ClientDisconnectedException) {

        }
        if ($this->followLocation && $this->receiver->isCompleted() && !empty($location = $this->receiver->getResponse()->getHeader('Location'))) {
            $this->receiver->reset();
            $this->follow($location);
        }
        return $this->client;
    }

    public function isCompleted(): bool
    {
        return $this->receiver->isCompleted() && empty($this->receiver->getResponse()->getHeader('Location'));
    }
}