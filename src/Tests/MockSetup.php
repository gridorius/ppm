<?php

namespace Tests;

use Closure;

class MockSetup
{
    public static function return($value): Closure
    {
        return function () use ($value) {
            return $value;
        };
    }

    public static function returnLink(&$value): Closure
    {
        return function () use (&$value) {
            return $value;
        };
    }

    public static function set(&$variable): Closure
    {
        return function ($value) use (&$variable) {
            $variable = $value;
        };
    }
}