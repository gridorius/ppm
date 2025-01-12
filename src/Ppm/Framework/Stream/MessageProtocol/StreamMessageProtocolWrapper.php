<?php

namespace Ppm\Framework\Stream\MessageProtocol;

use Ppm\Framework\Stream\Contracts\IStream;

class StreamMessageProtocolWrapper
{
    private IStream $stream;

    public function __construct(IStream $stream)
    {
        $this->stream = $stream;
    }

    public static function wrap(IStream $stream): static
    {
        return new static($stream);
    }

    public function send(string $message, array $headers = []): void
    {
        $preparedMessage = StreamMessageProtocol::prepareMessage($message, $headers);
        $totalLength = strlen($message);
        $written = 0;
        while ($written < $totalLength)
            $written += $this->stream->write(substr($preparedMessage, $written));
    }
}