<?php

namespace Ppm\Framework\Network\Client;

use Closure;
use Ppm\Framework\Network\HttpParserBase;

class ResponseDataParser extends HttpParserBase
{
    const TYPE_CHUNKED = 1;
    private HttpResponse $response;
    protected ?Closure $onProgress;
    protected int $responseType = 0;

    public function __construct(?callable $onProgress = null)
    {
        $this->response = new HttpResponse();
        $this->onProgress = is_null($onProgress) ? null : Closure::fromCallable($onProgress);
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
        $this->responseType = $this->response->getHeader('Transfer-Encoding') === 'chunked'
            ? static::TYPE_CHUNKED
            : 0;
    }

    protected function handleBody(): void
    {
        switch ($this->responseType) {
            case static::TYPE_CHUNKED:
                while (true)
                    if (!$this->readChunk())
                        break;
                break;
            default:
                if (!is_null($this->onProgress))
                    call_user_func($this->onProgress, $this->contentLength, $this->readLength);
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

    private function readChunk(): bool
    {
        [$line, $buffer] = explode("\r\n", $this->buffer, 2);
        $hex = trim($line);
        $size = hexdec($hex);

        if ($size == 0) {
            $this->stage = static::STATE_COMPLETED;
            return false;
        }

        $RNSize = $size + 2;
        if (strlen($buffer) >= $RNSize) {
            $chunk = substr($buffer, 0, $size);
            $this->response->addContent($chunk);
            $this->buffer = substr($buffer, $size + 2);
            return true;
        }
        return false;
    }
}