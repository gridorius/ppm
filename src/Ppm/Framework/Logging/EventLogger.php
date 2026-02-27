<?php

namespace Ppm\Framework\Logging;

class EventLogger
{
    protected static array $subscribers = [];

    public static function subscribe(string $source, callable $callback): void
    {
        if (!isset(static::$subscribers[$source]))
            static::$subscribers[$source] = [];
        static::$subscribers[$source][] = $callback;
    }

    public static function subscribeLogger(string $source, ILogger $logger): void
    {
        static::subscribe($source, function (string $level, string $message, array $fields) use ($logger) {
            if (method_exists($logger, $level))
                call_user_func([$logger, $level], $message, $fields);
        });
    }

    public static function log(string $source, string $level, string $message, array $fields = []): void
    {
        if (!isset(static::$subscribers[$source]))
            return;
        foreach (static::$subscribers[$source] as $callback)
            $callback($level, $message, $fields);
    }

    public static function toSource(string $source): SourceEventLogger
    {
        return new SourceEventLogger($source);
    }

    public static function trace(string $source, string $message, array $fields = []): void
    {
        static::log($source, 'trace', $message, $fields);
    }

    public static function debug(string $source, string $message, array $fields = []): void
    {
        static::log($source, 'debug', $message, $fields);
    }

    public static function info(string $source, string $message, array $fields = []): void
    {
        static::log($source, 'info', $message, $fields);
    }

    public static function warn(string $source, string $message, array $fields = []): void
    {
        static::log($source, 'warn', $message, $fields);
    }

    public static function error(string $source, string $message, array $fields = []): void
    {
        static::log($source, 'error', $message, $fields);
    }

    public static function fatal(string $source, string $message, array $fields = []): void
    {
        static::log($source, 'fatal', $message, $fields);
    }
}