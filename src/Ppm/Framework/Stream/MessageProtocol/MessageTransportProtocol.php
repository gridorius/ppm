<?php

namespace Ppm\Framework\Stream\MessageProtocol;

use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\Contracts\IStream;

class MessageTransportProtocol implements IMessageTransportProtocol
{
    protected array $handlers;
    protected array $partyHandlers;
    protected MessageSender $sender;
    protected MessageReceiver $receiver;
    protected StreamReadActionBind $bind;
    protected IStream $input;

    public function __construct(IStream $input, IStream $output, string $messageConverter = MessageConverter::class)
    {
        $this->handlers = [];
        $this->partyHandlers = [];
        $this->sender = MessageSender::wrap($output, $messageConverter);
        $this->receiver = new MessageReceiver(
            function () {
                $this->callMessageHandlers();
                $this->receiver->reset();
            },
            function (MessageReceiver $receiver, string $party) {
                $this->onReadyParty($receiver, $party);
            },
            $messageConverter
        );
        $this->input = $input;
    }

    public function onMessage(callable $callable): static
    {
        $this->handlers[] = $callable;
        return $this;
    }

    public function onMessageParty(callable $callable): static
    {
        $this->partyHandlers[] = $callable;
        return $this;
    }

    public function send(string $message, array $headers = []): void
    {
        $this->sender->send($message, $headers);
    }

    public function sendHeaders(array $headers): void
    {
        $this->sender->sendHeaders($headers);
    }

    protected function onReadyParty(MessageReceiver $receiver, string $party): void
    {
        foreach ($this->partyHandlers as $handler)
            call_user_func($handler, $receiver, $party);
    }

    protected function callMessageHandlers(): void
    {
        foreach ($this->handlers as $handler)
            call_user_func($handler, $this->receiver);
    }

    public function getBind(): StreamReadActionBind
    {
        return $this->bind;
    }
}