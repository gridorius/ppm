<?php

namespace Ppm\Framework\Logging;

class SourceEventLogger implements ILogger
{
    private string $source;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public function trace(string $message, array $fields = []): void
    {
        EventLogger::trace($this->source, $message, $fields);
    }

    public function debug(string $message, array $fields = []): void
    {
        EventLogger::debug($this->source, $message, $fields);
    }

    public function info(string $message, array $fields = []): void
    {
        EventLogger::info($this->source, $message, $fields);
    }

    public function warn(string $message, array $fields = []): void
    {
        EventLogger::warn($this->source, $message, $fields);
    }

    public function error(string $message, array $fields = []): void
    {
        EventLogger::error($this->source, $message, $fields);
    }

    public function fatal(string $message, array $fields = []): void
    {
        EventLogger::fatal($this->source, $message, $fields);
    }
}