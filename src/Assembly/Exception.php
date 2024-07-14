<?php

namespace Assembly;

class Exception extends \Exception
{
    public function __toString()
    {
        $class = get_class($this);
        return "Unexpected: {$class}({$this->getMessage()}, {$this->getCode()})\n" . static::prepareTraceAsString($this->getTrace());
    }

    public static function prepareTrace(array $trace): array
    {
        return array_map(function (array $trace) {
            $trace['file'] = static::preparePath($trace['file'] ?? $trace['class'] ?? $trace['function']);
            return $trace;
        }, $trace);
    }

    public static function prepareTraceAsString(array $trace): string
    {
        $trace = static::prepareTrace($trace);
        $string = '';
        foreach ($trace as $n => $item) {
            $class = $item['class'] ?? $item['object'] ?? '';
            $line = $item['line'] ?? '';
            $type = $item['type'] ?? '';
            $string .= "#$n {$item['file']}:{$line} | {$class}{$type}{$item['function']}\n";
        }
        return $string;
    }

    private static function preparePath(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }
}