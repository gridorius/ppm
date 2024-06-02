<?php

namespace Terminal;

use Exception;

class OptionParser
{
    public static function parse(array &$argv, array $options)
    {
        $optionValues = [];
        $end = false;
        while (!$end) {
            if (count($argv) == 0) {
                $end = true;
                continue;
            }

            $arg = $argv[0];
            if (substr($arg, 0, 2) == '--') {
                $option = substr($arg, 2);
                static::handleOption($option, $options, $argv, $optionValues);
            } else if (substr($arg, 0, 1) == '-') {
                $option = substr($arg, 1);
                if (strlen($option) > 1 && !preg_match("/=/", $option)) {
                    foreach (str_split($option) as $chainOption) {
                        if (!key_exists($chainOption, $options))
                            static::throwOptionException($option);

                        $optionValues[$chainOption] = empty($optionValues[$chainOption]) ? 1 : $optionValues[$chainOption] + 1;
                    }
                    array_shift($argv);
                } else {
                    static::handleOption($option, $options, $argv, $optionValues);
                }
            } else {
                $end = true;
            }
        }
        return $optionValues;
    }

    protected static function handleOption(string $option, array $options, array &$argv, array &$optionValues)
    {
        $value = null;
        if (preg_match("/=/", $option)) {
            [$option, $value] = explode('=', $option);
        }

        if (!key_exists($option, $options))
            static::throwOptionException($option);

        if ($options[$option] && empty($value)) {
            static::fillOption($optionValues, $option, $argv[1]);
            array_shift($argv);
            array_shift($argv);
        } else if ($options[$option]) {
            static::fillOption($optionValues, $option, $value);
            array_shift($argv);
        } else {
            $optionValues[$option] = empty($optionValues[$option]) ? 1 : $optionValues[$option] + 1;
            array_shift($argv);
        }
    }

    protected static function fillOption(array &$optionValues, string $option, string $value)
    {
        if (empty($optionValues[$option]))
            $optionValues[$option] = $value;
        else if (is_array($optionValues[$option]))
            $optionValues[$option][] = $value;
        else
            $optionValues[$option] = [$optionValues[$option], $value];
    }

    protected static function throwOptionException(string $option)
    {
        throw new Exception("Unexpected option {$option}");
    }
}