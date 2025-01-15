<?php

namespace Ppm\Framework\Stream\MessageProtocol;


use Ppm\Framework\Stream\Async\StreamReadActionBind;

interface IMessageTransportProtocol
{
    public function onMessage(callable $callable): static;

    public function onMessageParty(callable $callable): static;

    public function send(string $message, array $headers = []): void;

    public function getBind(): StreamReadActionBind;
}