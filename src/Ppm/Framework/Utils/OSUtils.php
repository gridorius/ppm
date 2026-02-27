<?php

namespace Ppm\Framework\Utils;

class OSUtils
{
    public static function kill(int $pid): void
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            exec("taskkill /F /PID $pid");
        } else {
            exec("kill -9 $pid");
        }
    }
}