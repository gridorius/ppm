<?php

namespace Ppm\Framework\Logging;

interface ILogger
{
    public function trace(string $message, array $fields = []): void;

    public function debug(string $message, array $fields = []): void;

    public function info(string $message, array $fields = []): void;

    public function warn(string $message, array $fields = []): void;

    public function error(string $message, array $fields = []): void;

    public function fatal(string $message, array $fields = []): void;
}