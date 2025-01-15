<?php

namespace Ppm\Framework\Stream\MessageProtocol;

use Closure;
use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\Contracts\IStream;

class MessageReceiver
{
    const STATE_LENGTH = 0;
    const STATE_HEADERS = 1;
    const STATE_MESSAGE = 2;
    const STATE_COMPLETED = 3;
    const STATE_ABORTED = 4;
    private string $headersString = '';
    private string $messageString = '';
    private array $headers = [];
    private int $remainderHeaderLength = 0;
    private int $remainderLength = 0;
    private int $state = self::STATE_LENGTH;
    private Closure $messageCallback;
    private ?Closure $partyCallback;
    private string $converterClass;

    public function __construct(callable $messageCallback, callable $partyCallback = null, string $converterClass = MessageConverter::class)
    {
        $this->messageCallback = Closure::fromCallable($messageCallback);
        $this->partyCallback = is_null($partyCallback) ? null : Closure::fromCallable($partyCallback);
        $this->converterClass = $converterClass;
    }

    public function createBind(IStream $stream): StreamReadActionBind
    {
        return StreamReadActionBind::create($stream, [
            $this,
            'onReadyData'
        ]);
    }

    public function onReadyData(IStream $stream): void
    {
        switch ($this->state) {
            case static::STATE_LENGTH:
                $lengthData = $stream->read($this->converterClass::LENGTH_HEADERS_SIZE + $this->converterClass::LENGTH_MESSAGE_SIZE);
                if (empty($lengthData)) {
                    $this->onAbort();
                    break;
                }
                $this->remainderHeaderLength = (int)substr($lengthData, 0, $this->converterClass::LENGTH_HEADERS_SIZE);
                $this->remainderLength = (int)substr($lengthData,
                        $this->converterClass::LENGTH_HEADERS_SIZE,
                        $this->converterClass::LENGTH_MESSAGE_SIZE) + $this->remainderHeaderLength;
                $this->state = $this->remainderHeaderLength > 0 ? static::STATE_HEADERS : static::STATE_MESSAGE;
                break;
            case static::STATE_HEADERS:
                $headersString = $stream->read($this->remainderHeaderLength);
                if (empty($headersString)) {
                    $this->onAbort();
                    break;
                }
                $readLength = strlen($headersString);
                $this->remainderLength -= $readLength;
                $this->remainderHeaderLength -= $readLength;
                $this->headersString .= $headersString;
                if ($this->remainderHeaderLength == 0) {
                    $this->headers = $this->converterClass::decodeHeaders($this->headersString);
                    $this->state = static::STATE_MESSAGE;
                }
                break;
            case static::STATE_MESSAGE:
                $data = $stream->read($this->remainderLength);
                if (empty($data)) {
                    $this->onAbort();
                    break;
                }
                $this->messageString .= $data;
                $this->remainderLength -= strlen($data);
                if (!is_null($this->partyCallback))
                    call_user_func($this->partyCallback, $this, $data);
                if ($this->remainderLength == 0) {
                    $this->state = static::STATE_COMPLETED;
                    call_user_func($this->messageCallback, $this);
                }
                break;
        }
    }

    public function getRemainderLength(): int
    {
        return $this->remainderLength;
    }

    public function isCompleted(): bool
    {
        return $this->state === static::STATE_COMPLETED;
    }

    public function isAborted(): bool
    {
        return $this->state === static::STATE_ABORTED;
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

    private function onAbort(): void
    {
        $this->state = static::STATE_ABORTED;
        $this->headers['aborted'] = true;
        if (!is_null($this->partyCallback))
            call_user_func($this->partyCallback, $this, '');
        call_user_func($this->messageCallback, $this);
    }
}