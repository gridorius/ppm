<?php

namespace Ppm\Framework\Terminal\Options;

use Exception;

class OptionParser
{
    /**
     * [
     *      "counters": [
     *          "a",
     *          "b",
     *          "long"
     *      ],
     *      "value": [
     *          "name",
     *          "c"
     *      ]
     * ]
     *
     * @param array $definedOptions
     */

    private array $counters;
    private array $values;

    public function __construct(array $definedOptions = [])
    {
        $this->counters = [];
        $this->values = [];
        $this->addDefinedOptions($definedOptions);
    }

    public function addDefinedOptions(array $definedOptions): void
    {
        $this->counters = array_merge($this->counters, $definedOptions['counters'] ?? []);
        $this->values = array_merge($this->values, $definedOptions['values'] ?? []);
    }

    public function parse(&$arguments): array
    {
        $optionBuilder = new OptionCollection();
        while (true) {
            if (empty($arguments)) break;
            $argument = $arguments[0];
            if (str_starts_with($argument, '--')) {
                $this->parseLong($argument, $arguments, $optionBuilder);
            } else if (str_starts_with($argument, '-')) {
                $this->parseShort($argument, $arguments, $optionBuilder);
            } else {
                break;
            }
        }
        return $optionBuilder->getOptions();
    }

    private function parseLong(string $argument, array &$arguments, OptionCollection $options): void
    {
        $option = substr($argument, 2);
        if ($this->containsValue($argument))
            $this->parseKeyValue($option, $arguments, $options);
        else
            $this->parseOption($option, $arguments, $options);
    }

    private function parseShort(string $argument, array &$arguments, OptionCollection $options): void
    {
        $option = substr($argument, 1);
        if ($this->containsValue($argument))
            $this->parseKeyValue($option, $arguments, $options);
        else if (strlen($option) == 1)
            $this->parseOption($option, $arguments, $options);
        else {
            $counters = str_split($option);
            foreach ($counters as $counter)
                if (in_array($counter, $this->counters))
                    $options->incrementOption($counter);
                else
                    $this->throwUnexpectedOption($counter);
            array_shift($arguments);
        }
    }

    private function parseKeyValue(string $option, array &$arguments, OptionCollection $options): void
    {
        [$key, $value] = $this->parseKeyVal($option);
        if (!in_array($key, $this->values))
            $this->throwUnexpectedOption($option);

        array_shift($arguments);
        $options->setValue($key, $value);
    }

    private function parseOption(string $option, array &$arguments, OptionCollection $options): void
    {
        if (in_array($option, $this->counters)) {
            $options->incrementOption($option);
            array_shift($arguments);
        } elseif (in_array($option, $this->values)) {
            if (empty($arguments[1]))
                throw new Exception("Invalid value for {$option}");
            array_shift($arguments);
            $value = array_shift($arguments);
            $options->setValue($option, $value);
        } else {
            $this->throwUnexpectedOption($option);
        }
    }

    private function containsValue(string $argument): bool
    {
        return str_contains($argument, "=");
    }

    private function parseKeyVal(string $argument): array
    {
        return explode('=', $argument);
    }

    private function throwUnexpectedOption(string $argument): void
    {
        throw new Exception("Unexpected option {$argument}");
    }
}