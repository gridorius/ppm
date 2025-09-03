<?php

namespace Ppm\Framework\Network\Client;

use Closure;
use Ppm\Framework\Stream\Async\AsyncStreamWatcher;
use Ppm\Framework\Stream\Async\IBindable;
use Ppm\Framework\Stream\Async\MainCycle;
use Ppm\Framework\Stream\Async\Promise;
use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\Contracts\IStream;

class HttpRequestAction implements IBindable
{
    private HttpSocketClient $client;
    private ?Closure $uploadProgressHandler;
    private ?Closure $downloadProgressHandler;
    private HttpRequest $request;
    private ResponseDataParser $receiver;
    private StreamReadActionBind $binding;
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
        $this->binding->setStream($this->client);
    }

    public function wait(?callable $downloadProgressHandler = null, bool $followLocation = true): HttpResponse
    {
        $this->downloadProgressHandler = $downloadProgressHandler;
        $bind = $this->creteBind($downloadProgressHandler);
        $async = new AsyncStreamWatcher([$bind]);
        $this->followLocation = $followLocation;

        while (!$this->isCompleted())
            $async->watch();
        return $this->getResponse();
    }

    public function waitAsync(?callable $downloadProgressHandler = null): Promise
    {
        $this->downloadProgressHandler = $downloadProgressHandler;
        $this->receiver = new ResponseDataParser($downloadProgressHandler);

        return new Promise(function ($resolve) {
            MainCycle::watch((function () use ($resolve) {
                yield $this->client;
                while (!$this->receiver->isCompleted())
                    $this->receiver->onReadyContent($this->client);
                $resolve($this->receiver->getResponse());
            })());
        });
    }

    public function creteBind(?callable $downloadProgressHandler = null): StreamReadActionBind
    {
        $this->downloadProgressHandler = $downloadProgressHandler;
        $this->receiver = new ResponseDataParser($downloadProgressHandler);
        return $this->binding = $this->bind($this->client);
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
        if ($this->followLocation && $this->receiver->isCompleted() && !empty($location = $this->receiver->getResponse()->getHeader('Location'))) {
            $this->follow($location);
            $this->receiver = new ResponseDataParser($this->downloadProgressHandler);
        }
    }

    public function isCompleted(): bool
    {
        return $this->receiver->isCompleted();
    }
}