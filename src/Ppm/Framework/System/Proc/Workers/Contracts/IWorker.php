<?php

namespace Ppm\Framework\System\Proc\Workers\Contracts;

use Ppm\Framework\Stream\Async\StreamReadActionBind;

interface IWorker
{
    public function onMessage(callable $callable): static;

    public function onMessageParty(callable $callable): static;

    public function send(string $message, array $headers = []): void;
}