<?php

namespace Ppm\Framework\Exceptions;

use Exception;

class ErrorsException extends Exception
{
    private array $errors = [];

    /**
     * @param array $errors
     */
    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
        parent::__construct(implode(";\n", $errors));
    }

    public function addError(string $message): static
    {
        $this->errors[] = $message;
        $this->updateMessage();
        return $this;
    }

    public function setErrors(array $errors): static
    {
        $this->errors = $errors;
        $this->updateMessage();
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function updateMessage(): void
    {
        $this->message = implode(";\n", $this->errors);
    }
}