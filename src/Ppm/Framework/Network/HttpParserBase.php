<?php

namespace Ppm\Framework\Network;

use Ppm\Framework\Stream\Contracts\IStreamRead;

abstract class HttpParserBase
{
    const STATE_HEADER = 1;
    const STAGE_BODY = 2;
    const STATE_COMPLETED = 3;
    protected int $stage = self::STATE_HEADER;
    protected string $buffer = '';
    protected ?int $contentLength = null;
    protected int $readLength = 0;

    public function onReadyContent(IStreamRead $stream): void
    {
        $data = $stream->read(5000);
        if (empty($data))
            throw new ClientDisconnectedException();
        $this->buffer .= $data;
        if ($stream->eof()) {
            $this->stage = static::STATE_COMPLETED;
            return;
        }
        switch ($this->stage) {
            case static::STATE_HEADER:
                if (str_contains($this->buffer, "\r\n\r\n")) {
                    [$headers, $body] = explode("\r\n\r\n", $this->buffer, 2);
                    $headerLines = explode("\r\n", $headers);
                    $this->parseHeaders($headerLines);
                    $this->buffer = $body;
                    $this->readLength = strlen($body);
                    if (empty($body))
                        $this->contentLength = 0;
                    $this->stage = static::STAGE_BODY;
                    $this->onParsedHeaders();
                    $this->handleBody();
                }
                break;
            case static::STAGE_BODY:
                $this->readLength += strlen($data);
                $this->handleBody();
                break;
        }

    }

    public function reset(): void
    {
        $this->stage = static::STATE_HEADER;
        $this->buffer = '';
        $this->contentLength = null;
        $this->readLength = 0;
    }

    public function isCompleted(): bool
    {
        return $this->stage == static::STATE_COMPLETED;
    }

    protected function parseHeaders(array $headerLines): void
    {
        $first = array_shift($headerLines);
        $this->parseFirstLine($first);
        foreach ($headerLines as $line) {
            [$name, $value, $options] = static::parseRawHeader($line);
            $this->handleHeader($name, $value, $options);
            if ($name == 'Content-Length')
                $this->contentLength = intval($value);
        }
    }

    public static function parseRawHeader(string $header): array
    {
        [$header, $headerValue] = explode(':', $header, 2);
        $options = [];
        if (str_contains($headerValue, ";")) {
            $optionsData = explode(';', $headerValue);
            foreach ($optionsData as $option) {
                if (str_contains($option, "=")) {
                    [$key, $value] = explode('=', $option, 2);
                    $options[trim($key)] = trim($value);
                } else {
                    $options[trim($option)] = true;
                }
            }
            return [$header, trim($headerValue), $options];
        }
        return [$header, trim($headerValue), []];
    }

    protected function bodyEnded(): bool
    {
        return !is_null($this->contentLength) && $this->readLength >= $this->contentLength;
    }

    abstract protected function parseFirstLine(string $line): void;

    abstract protected function onParsedHeaders(): void;

    abstract protected function handleHeader(string $name, string $value, array $options): void;

    abstract protected function handleBody(): void;
}