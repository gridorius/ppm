<?php

namespace Ppm\Framework\System;

class IO
{
    public static function Write(string $data): void
    {
        fwrite(STDOUT, $data);
    }

    public static function WriteLine(string $data): void
    {
        static::Write($data . PHP_EOL);
    }

    public static function read(): string
    {
        return fgets(STDIN);
    }

    public static function Error(string $data): void
    {
        fwrite(STDERR, $data);
    }

    public static function ErrorLine(string $data): void
    {
        fwrite(STDERR, $data . PHP_EOL);
    }
}