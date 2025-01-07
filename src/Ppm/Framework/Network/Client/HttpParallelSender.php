<?php

namespace Ppm\Framework\Network\Client;

use Ppm\Framework\Stream\Async\AsyncStreamWatcher;

class HttpParallelSender
{
    /**
     * @var HttpRequestAction[]
     */
    private array $actions;

    public function __construct(array $actions)
    {
        $this->actions = $actions;
    }

    public function send(callable $uploadProgressHandler = null): static
    {
        foreach ($this->actions as $action)
            $action->send($uploadProgressHandler);
        return $this;
    }

    public function waitResponses(callable $downloadProgressHandler = null, bool $followLocation = true): array
    {
        $bindings = [];
        foreach ($this->actions as $action)
            $bindings[] = $action->creteBind($downloadProgressHandler);
        $async = new AsyncStreamWatcher($bindings);

        while (!$this->isCompleted()) {
            $async->watch();
        }

        $responses = [];
        foreach ($this->actions as $action)
            $responses[] = $action->getResponse();

        return $responses;
    }

    public function isCompleted(): bool
    {
        $completed = true;
        foreach ($this->actions as $action)
            if (!$action->isCompleted())
                $completed = false;
        return $completed;
    }
}