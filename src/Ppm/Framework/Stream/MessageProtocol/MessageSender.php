<?php

namespace Ppm\Framework\Stream\MessageProtocol;

use Ppm\Framework\Stream\Contracts\IStream;

class MessageSender
{
    private IStream $stream;
    private string $messageConverter;

    public function __construct(IStream $stream, string $messageConverter = MessageConverter::class)
    {
        $this->stream = $stream;
        $this->messageConverter = $messageConverter;
    }

    public static function wrap(IStream $stream, string $messageConverter = MessageConverter::class): static
    {
        return new static($stream, $messageConverter);
    }

    public function send(string $message, array $headers = []): void
    {
        $preparedMessage = $this->messageConverter::convert($message, $headers);
        $totalLength = strlen($message);
        $written = 0;
        while ($written < $totalLength)
            $written += $this->stream->write(substr($preparedMessage, $written));
    }
}