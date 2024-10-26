<?php

namespace Ppm\Framework\Network\Client;

use Generator;
use Ppm\Framework\Network\HttpParserBase;
use Ppm\Framework\Stream\IStream;

class ResponseDataParser extends HttpParserBase
{
    const STATE_CHUNKED = 4;
    private HttpResponse $response;
    private $onProgress;

    public function __construct(HttpSocketClient $stream, callable $onProgress = null)
    {
        parent::__construct($stream);
        $this->response = new HttpResponse();
        $this->onProgress = $onProgress;
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

    protected function onEndHeaders(): void
    {
        $this->state = $this->response->getHeader('Transfer-Encoding') === 'chunked'
            ? static::STATE_CHUNKED
            : static::STAGE_BODY;
    }

    protected function handleBody(): void
    {
        if ($this->stream->eof()) {
            $this->state = static::STATE_COMPLETED;
            return;
        }

        switch ($this->state) {
            case static::STATE_CHUNKED:
                $this->readChunk();
                break;
            default:
                $content = $this->stream->read();
                if (empty($content)) {
                    $this->state = static::STATE_COMPLETED;
                    return;
                }

                $this->response->addContent($content);
                if (!is_null($this->onProgress))
                    call_user_func($this->onProgress, (int)$this->response->getheader('Content-Length'), $this->response->getLength());
                if ((int)$this->response->getheader('Content-Length') <= $this->response->getLength()) {
                    $this->state = static::STATE_COMPLETED;
                    $this->stream->read();
                }
        }
    }

    public function isCompleted(): bool
    {
        return $this->state == static::STATE_COMPLETED;
    }

    private function readChunk(): void
    {
        $line = $this->stream->readLine();
        $hex = trim($line);
        $size = hexdec($hex);

        if ($size == 0) {
            $this->state = static::STATE_COMPLETED;
            return;
        }

        $size += 2;
        $chunk = $this->stream->read($size);
        $loadedLength = strlen($chunk);
        while ($loadedLength < $size) {
            $chunk .= $this->stream->read($size - $loadedLength);
            $loadedLength = strlen($chunk);
        }
        $chunk = substr($chunk, 0, -2);
        $this->response->addContent($chunk);
    }
}