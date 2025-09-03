<?php

namespace Ppm\Framework\Network\Client;

use Closure;
use Ppm\Framework\Network\HttpParserBase;
use Ppm\Framework\Stream\Async\IBindable;
use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\Contracts\IStream;
use Ppm\Framework\Stream\Contracts\IStreamRead;

class ResponseDataParser extends HttpParserBase implements IBindable
{
    const STATE_CHUNKED = 4;
    private HttpResponse $response;
    protected ?Closure $onProgress;

    public function __construct(?callable $onProgress = null)
    {
        $this->response = new HttpResponse();
        $this->onProgress = is_null($onProgress) ? null : Closure::fromCallable($onProgress);
    }

    public function bind(IStream $stream): StreamReadActionBind
    {
        return StreamReadActionBind::create($stream, [
            $this,
            'onReadyContent'
        ]);
    }

    public function reset(): void
    {
        parent::reset();
        $this->response = new HttpResponse();
    }

    public function getResponse(): HttpResponse
    {
        return $this->response;
    }

    protected function parseFirstLine(string $line): void
    {
        $statusCode = explode(' ', $line, 3);
        $this->response->setStatusCode((int)$statusCode[1], $statusCode[2]);
    }

    protected function handleHeader(string $name, string $value, array $options): void
    {
        $this->response
            ->setHeader($name, $value)
            ->setHeaderOption($name, $options);
    }

    protected function onParsedHeaders(): void
    {
        $this->stage = $this->response->getHeader('Transfer-Encoding') === 'chunked'
            ? static::STATE_CHUNKED
            : static::STAGE_BODY;
    }

    protected function handleBody(): void
    {
        switch ($this->stage) {
            case static::STATE_CHUNKED:
                $this->readChunk();
                break;
            default:
                if (!is_null($this->onProgress))
                    call_user_func($this->onProgress, $this->contentLength, strlen($this->buffer));
        }
        if ($this->bodyEnded()) {
            $this->stage = static::STATE_COMPLETED;
            $this->response->addContent($this->buffer);
        }
    }

    public function isCompleted(): bool
    {
        return $this->stage == static::STATE_COMPLETED;
    }

    private function readChunk(): void
    {
        [$line, $buffer] = explode("\r\n", $this->buffer, 2);
        $hex = trim($line);
        $size = hexdec($hex);

        if ($size == 0) {
            $this->stage = static::STATE_COMPLETED;
            return;
        }

        $RNSize = $size + 2;
        if (strlen($buffer) >= $RNSize) {
            $chunk = substr($buffer, 0, $size);
            $this->response->addContent($chunk);
            $this->buffer = substr($this->buffer, strlen($line) + 2 + $RNSize);
        }
    }
}