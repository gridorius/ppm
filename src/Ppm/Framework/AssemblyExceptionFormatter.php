<?php

namespace Ppm\Framework;

use Throwable;

class AssemblyExceptionFormatter
{
    public static function prepareTrace(Throwable $exception): array
    {
        return array_map(function (array $trace) {
            $trace['file'] = static::preparePath($trace['file'] ?? $trace['class'] ?? $trace['function']);
            return $trace;
        }, $exception->getTrace());
    }

    public static function prepareTraceAsString(Throwable $exception): string
    {
        $trace = static::prepareTrace($exception);
        $string = '';
        foreach ($trace as $n => $item) {
            $class = $item['class'] ?? $item['object'] ?? '';
            $line = $item['line'] ?? '';
            $type = $item['type'] ?? '';
            $string .= "#$n {$item['file']}({$line}): {$class}{$type}{$item['function']}()" . PHP_EOL;
        }
        return $string;
    }

    public static function showExceptionEndExit(Throwable $exception): void
    {
        echo PHP_EOL . get_class($exception) . ': '
            . $exception->getMessage() . ' in '
            . static::preparePath($exception->getFile()) . ':' . $exception->getLine() . PHP_EOL;
        echo static::prepareTraceAsString($exception);
        exit(1);
    }

    public static function formatError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        echo 'Error: ' . $errstr . ' in '
            . static::preparePath($errfile) . ':' . $errline . PHP_EOL;
    }

    public static function preparePath(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }
}