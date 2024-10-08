<?php

namespace Ppm\Framework;

class CurrentAssembly
{
    private static Assembly $assembly;

    public static function getAssembly(): Assembly
    {
        if (empty(static::$assembly))
            static::$assembly = new Assembly();
        return static::$assembly;
    }
}