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
        parent::__construct();
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

    protected function onHeadersEnded(): void
    {
        $this->state = $this->response->getHeader('Transfer-Encoding') === 'chunked'
            ? static::STATE_CHUNKED
            : static::STAGE_BODY;
    }

    public function onReadyContent(IStreamRead $stream): void
    {
        parent::onReadyContent($stream);
    }

    protected function handleBody(IStreamRead $stream): void
    {
        if ($stream->eof()) {
            $this->state = static::STATE_COMPLETED;
            return;
        }

        switch ($this->state) {
            case static::STATE_CHUNKED:
                $this->readChunk($stream);
                break;
            default:
                $content = $stream->read();
                if (empty($content)) {
                    $this->state = static::STATE_COMPLETED;
                    return;
                }

                $this->response->addContent($content);
                $contentLength = (int)$this->response->getheader('Content-Length');
                $responseLength = $this->response->getLength();
                if (!is_null($this->onProgress))
                    call_user_func($this->onProgress, $contentLength, $responseLength);
                if ($contentLength <= $responseLength) {
                    $this->state = static::STATE_COMPLETED;
                    $stream->read();
                }
        }
    }

    public function isCompleted(): bool
    {
        return $this->state == static::STATE_COMPLETED;
    }

    private function readChunk(IStreamRead $stream): void
    {
        $line = $stream->readLine();
        $hex = trim($line);
        $size = hexdec($hex);

        if ($size == 0) {
            $this->state = static::STATE_COMPLETED;
            return;
        }

        $size += 2;
        $chunk = $stream->read($size);
        $loadedLength = strlen($chunk);
        while ($loadedLength < $size) {
            $chunk .= $stream->read($size - $loadedLength);
            $loadedLength = strlen($chunk);
        }
        $chunk = substr($chunk, 0, -2);
        $this->response->addContent($chunk);
    }
}