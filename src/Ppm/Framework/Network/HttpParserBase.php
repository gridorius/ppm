<?php

namespace Ppm\Framework\Network;

use Ppm\Framework\Stream\ResourceStream;

abstract class HttpParserBase
{
    const STATE_HEADER = 1;
    const STAGE_BODY = 2;
    const STATE_COMPLETED = 3;
    protected ResourceStream $stream;
    protected int $state;
    protected int $line = 0;

    public function __construct(ResourceStream $stream)
    {
        $this->stream = $stream;
        $this->state = static::STATE_HEADER;
    }

    public function handleInput(): void
    {
        switch ($this->state) {
            case static::STATE_HEADER:
                $this->handleHeaders();
                break;
            default:
                $this->handleBody();
        }
    }

    public function isCompleted(): bool
    {
        return $this->state == static::STATE_COMPLETED;
    }

    protected function handleHeaders(): void
    {
        $line = trim($this->stream->readLine());
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
        [$header, $value] = explode(':', $header, 2);
        $options = [];
        if (str_contains($value, ";") || str_contains($value, "=")) {
            $optionsData = explode(';', $value);
            foreach ($optionsData as $option) {
                if (str_contains($option, "=")) {
                    [$key, $value] = explode('=', $option);
                    $options[trim($key)] = trim($value);
                } else {
                    $options[trim($option)] = true;
                }
            }
        } else {
            return [$header, trim($value), []];
        }
        return [$header, trim($value), $options];
    }

    abstract protected function parseFirstLine(string $line): void;

    abstract protected function handleHeader(string $name, string $value, array $options): void;

    abstract protected function onHeadersEnded(): void;

    abstract protected function handleBody(): void;
}