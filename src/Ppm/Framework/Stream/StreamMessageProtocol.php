<?php

namespace Ppm\Framework\Stream;

use Ppm\Framework\Stream\Contracts\IStream;

class StreamMessageProtocol
{
    const STATE_LENGTH = 0;
    const STATE_HEADERS = 1;
    const STATE_MESSAGE = 2;
    const STATE_COMPLETED = 3;
    const LENGTH_HEADERS_SIZE = 6;
    const LENGTH_MESSAGE_SIZE = 16;
    protected string $headersString = '';
    protected string $messageString = '';
    protected array $headers = [];
    protected int $remainderHeaderLength = 0;
    protected int $remainderLength = 0;
    protected int $state = self::STATE_LENGTH;

    public static function prepareMessage(string $message, array $headers): string
    {
        $headersString = http_build_query($headers);
        $headerLength = str_pad(strlen($headersString), static::LENGTH_HEADERS_SIZE, ' ', STR_PAD_LEFT);
        $messageLength = str_pad(strlen($message), static::LENGTH_MESSAGE_SIZE, ' ', STR_PAD_LEFT);
        return $headerLength . $messageLength . $headersString . $message;
    }

    public function onReadyData(IStream $stream, callable $onMessageParty = null): void
    {
        switch ($this->state) {
            case static::STATE_LENGTH:
                $lengthData = $stream->read(static::LENGTH_HEADERS_SIZE + static::LENGTH_MESSAGE_SIZE);
                if (empty($lengthData)) {
                    $this->state = static::STATE_COMPLETED;
                    break;
                }
                $this->remainderHeaderLength = (int)substr($lengthData, 0, static::LENGTH_HEADERS_SIZE);
                $this->remainderLength = (int)substr($lengthData, static::LENGTH_HEADERS_SIZE, static::LENGTH_MESSAGE_SIZE) + $this->remainderHeaderLength;
                $this->state = self::STATE_HEADERS;
                break;
            case static::STATE_HEADERS:
                $headersString = $stream->read($this->remainderHeaderLength);
                $readLength = strlen($headersString);
                $this->remainderLength -= $readLength;
                $this->remainderHeaderLength -= $readLength;
                $this->headersString .= $headersString;
                if ($this->remainderHeaderLength == 0) {
                    parse_str($this->headersString, $this->headers);
                    $this->state = static::STATE_MESSAGE;
                }
                break;
            case static::STATE_MESSAGE:
                $data = $stream->read($this->remainderLength);
                $this->messageString .= $data;
                $this->remainderLength -= strlen($data);
                if (!is_null($onMessageParty))
                    call_user_func($onMessageParty, $data, $this->headers, $this->remainderLength);
                if ($this->remainderLength == 0)
                    $this->state = static::STATE_COMPLETED;
                break;
        }
    }

    public function isCompleted(): bool
    {
        return $this->state === static::STATE_COMPLETED;
    }

    public function getMessage(): string
    {
        return $this->messageString;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function reset(): void
    {
        $this->state = static::STATE_LENGTH;
        $this->remainderLength = 0;
        $this->remainderHeaderLength = 0;
        $this->headersString = '';
        $this->messageString = '';
        $this->headers = [];
    }
}