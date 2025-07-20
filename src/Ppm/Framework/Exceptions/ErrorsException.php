<?php

namespace Ppm\Framework\Exceptions;

use Exception;

class ErrorsException extends Exception
{
    private array $errors = [];

    public function addError(string $message): static
    {
        $this->errors[] = $message;
        return $this;
    }

    public function setErrors(array $errors): static
    {
        $this->errors = $errors;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}