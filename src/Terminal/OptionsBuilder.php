<?php

namespace Terminal;

use Exception;

class OptionsBuilder
{
    private array $options;

    public function __construct(array $definedOptions)
    {
        $this->options = [];
        foreach ($definedOptions as $option => $withValue) {
            if ($withValue) {
                $this->options[$option] = [];
            } else {
                $this->options[$option] = 0;
            }
        }
    }

    public function setValue(string $option, string $value): void
    {
        $this->options[$option][] = $value;
    }

    public function incrementOption(string $option): void
    {
        $this->options[$option]++;
    }

    public function build(): array
    {
        $resultOptions = [];
        foreach ($this->options as $option => $value) {
            if (is_array($value)) {
                if (empty($value)) {
                    $resultOptions[$option] = null;
                } elseif (count($value) == 1) {
                    $resultOptions[$option] = reset($value);
                } else {
                    $resultOptions[$option] = $value;
                }
            } else {
                $resultOptions[$option] = $value;
            }
        }
        return $resultOptions;
    }
}