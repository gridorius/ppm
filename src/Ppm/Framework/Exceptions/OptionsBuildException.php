<?php

namespace Ppm\Framework\Exceptions;

use Exception;

class OptionsBuildException extends Exception
{
    private array $fieldErrors = [];

    public function __construct(array $fieldErrors)
    {
        $this->fieldErrors = $fieldErrors;
        parent::__construct("Options build error", 0, null);
    }

    public function getFieldErrors(): array
    {
        return $this->fieldErrors;
    }

    public function formatFieldErrors(string $invalid, string $notNull): array
    {
        $result = [];
        foreach ($this->fieldErrors as $field => $type) {
            switch ($type[0]) {
                case 'invalid':
                    $result[] = str_replace(['FIELD', 'EXPECTED', 'PASSED'], [$field, $type[1], $type[2]], $invalid);
                    break;
                case 'null':
                    $result[] = str_replace(['FIELD', 'EXPECTED'], [$field, $type[1]], $notNull);;
                    break;
            }
        }
        return $result;
    }
}