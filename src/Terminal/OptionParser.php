<?php

namespace Terminal;

use Exception;

class OptionParser
{
    private array $definedOptions;

    public function __construct(array $definedOptions = [])
    {
        $this->definedOptions = $definedOptions;
    }

    public function setDefinedOptions(array $definedOptions): void{
        $this->definedOptions = $definedOptions;
    }

    public function parse(&$arguments): array
    {
        $optionBuilder = new OptionsBuilder($this->definedOptions);
        while (true) {
            if (empty($arguments)) break;

            $argument = $arguments[0];
            if (str_starts_with($argument, '--')) {
                $optionValue = explode('=', substr($argument, 2));
                array_shift($arguments);
                $this->parseLongOption($optionBuilder, $arguments, $optionValue[0], $optionValue[1] ?? null);
            } else if (str_starts_with($argument, '-')) {
                $optionValue = explode('=', substr($argument, 1));
                array_shift($arguments);
                $this->parseShortOption($optionBuilder, $arguments, $optionValue[0], $optionValue[1] ?? null);
            } else {
                break;
            }
        }
        return $optionBuilder->build();
    }

    private function parseLongOption(OptionsBuilder $optionsBuilder, array &$arguments, string $option, ?string $value): void
    {
        $this->throwIfInvalid($option);
        if ($this->definedOptions[$option]) {
            $optionsBuilder->setValue(
                $option,
                $this->getOptionValue($option, $arguments, $value)
            );
        } else {
            $optionsBuilder->incrementOption($value);
        }
    }

    private function parseShortOption(OptionsBuilder $optionsBuilder, array &$arguments, string $option, ?string $value): void
    {
        if (!is_null($value) && strlen($option) > 1) {
            throw new Exception("Invalid short option -{$option}");
        } else if (strlen($option) > 1) {
            foreach (str_split($option) as $chainOption) {
                $this->throwIfInvalid($chainOption);
                if ($this->definedOptions[$chainOption])
                    throw new Exception("Unexpected option -{$option}");
                $optionsBuilder->incrementOption($chainOption);
            }
        } else {
            if ($this->definedOptions[$option]) {
                $optionsBuilder->setValue(
                    $option,
                    $this->getOptionValue($option, $arguments, $value)
                );
            } else {
                $optionsBuilder->incrementOption($option);
            }
        }
    }

    private function getOptionValue(string $option, array &$arguments, ?string $value = null)
    {
        return is_null($value) ? array_shift($arguments) : $value;
    }

    private function throwIfInvalid(string $option): void
    {
        if (!key_exists($option, $this->definedOptions))
            throw new Exception("Unexpected option -{$option}");
    }
}