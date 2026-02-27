<?php

namespace Ppm\Framework\Exceptions;

use Exception;

class OptionParseException extends Exception
{
    private string $expectedType;
    private string $passedType;

    public function __construct(string $expectedType, string $passedType)
    {
        $this->expectedType = $expectedType;
        $this->passedType = $passedType;
        parent::__construct("Expected type {$expectedType}", 0, null);
    }

    public function getExpectedType(): string
    {
        return $this->expectedType;
    }

    public function getPassedType(): string
    {
        return $this->passedType;
    }
}