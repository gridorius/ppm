<?php

namespace Ppm\Framework\Network;

use Ppm\Framework\Stream\Contracts\IStreamRead;

abstract class HttpParserBase
{
    const STATE_HEADER = 1;
    const STAGE_BODY = 2;
    const STATE_COMPLETED = 3;
    protected int $state;
    protected int $line = 0;

    public function __construct()
    {
        $this->state = static::STATE_HEADER;
    }

    public function onReadyContent(IStreamRead $stream): void
    {
        switch ($this->state) {
            case static::STATE_HEADER:
                $this->handleHeaders($stream);
                break;
            default:
                $this->handleBody($stream);
        }
    }

    public function isCompleted(): bool
    {
        return $this->state == static::STATE_COMPLETED;
    }

    protected function handleHeaders(IStreamRead $stream): void
    {
        $line = trim($stream->readLine());
        if (empty($line)) {
            $this->state = static::STAGE_BODY;
            $this->onHeadersEnded();
            return;
        }

        if ($this->line === 0)
            $this->parseFirstLine($line);
        else {
            [$name, $value, $options] = static::parseRawHeader($line);
            $this->handleHeader($name, $value, $options);
        }

        $this->line++;
    }

    public static function parseRawHeader(string $header): array
    {
        [$header, $headerValue] = explode(':', $header, 2);
        $options = [];
        if (str_contains($headerValue, ";") || str_contains($headerValue, "=")) {
            $optionsData = explode(';', $headerValue);
            foreach ($optionsData as $option) {
                if (str_contains($option, "=")) {
                    [$key, $value] = explode('=', $option);
                    $options[trim($key)] = trim($value);
                } else {
                    $options[trim($option)] = true;
                }
            }
            return [$header, trim($headerValue), $options];
        }
        return [$header, trim($headerValue), []];
    }

    public function reset(): void
    {
        $this->state = static::STATE_HEADER;
    }

    public function onCompleted(): void
    {

    }

    abstract protected function parseFirstLine(string $line): void;

    abstract protected function handleHeader(string $name, string $value, array $options): void;

    abstract protected function onHeadersEnded(): void;

    abstract protected function handleBody(IStreamRead $stream): void;
}