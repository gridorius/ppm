<?php

namespace Tests;

class Assert
{
    const EXPECTED_ACTUAL_TEMPLATE = "%s\n    Expected: %s\n    Actual  : %s";
    private static string $source;
    private static array $assertsResults = [];

    private static bool $failed = false;

    public static function setSource(string $source): void
    {
        static::$source = $source;
    }

    public static function isTrue(bool $actual, ?string $message = null): void
    {
        $actual
            ? static::addSuccess()
            : static::addFailure($message ?? sprintf(static::EXPECTED_ACTUAL_TEMPLATE, 'isTrue', 'true', 'false'));
    }

    public static function isFalse(bool $actual, ?string $message = null): void
    {

        $actual
            ? static::addFailure($message ?? sprintf(static::EXPECTED_ACTUAL_TEMPLATE, 'isFalse', 'false', 'true'))
            : static::addSuccess();
    }

    public static function isNull($actual, ?string $message = null): void
    {

        is_null($actual)
            ? static::addSuccess()
            : static::addFailure($message ?? sprintf(static::EXPECTED_ACTUAL_TEMPLATE, 'isNull', 'null', static::prepareOutput($actual)));
    }

    public static function notNull($actual, ?string $message = null): void
    {
        is_null($actual)
            ? static::addFailure($message ?? sprintf(static::EXPECTED_ACTUAL_TEMPLATE, 'notNull', 'not null', static::prepareOutput($actual)))
            : static::addSuccess();
    }

    public static function isEquals(int|float|string|object $expected, int|float|string|object $actual, ?string $message = null): void
    {
        $expected === $actual
            ? static::addSuccess()
            : static::addFailure($message ?? sprintf(
            static::EXPECTED_ACTUAL_TEMPLATE,
            'isEquals',
            static::prepareOutput($expected),
            static::prepareOutput($actual)));
    }

    public static function isNotEquals(int|float|string|object $expected, int|float|string|object $actual, ?string $message = null): void
    {
        $expected === $actual
            ? static::addFailure($message ?? sprintf(
            static::EXPECTED_ACTUAL_TEMPLATE,
            'isNotEquals',
            static::prepareOutput($expected),
            static::prepareOutput($actual)))
            : static::addSuccess();
    }

    public static function isInstanceOf(object $actual, string $expectedType): void
    {
        $actual instanceof $expectedType
            ? static::addSuccess()
            : static::addFailure($message ?? sprintf(static::EXPECTED_ACTUAL_TEMPLATE, 'isInstanceOf', $expectedType, get_class($actual)));
    }

    public static function arrayEquals(array $expected, array $actual, ?string $message = null): void
    {
        $fail = false;
        if (count($expected) != count($actual)) {
            $fail = true;
        } else {
            foreach ($expected as $key => $value) {
                if ($actual[$key] !== $value) {
                    $fail = true;
                    break;
                }
            }
        }

        $fail
            ? static::addFailure($message ?? "Arrays not identical.\n" . var_export($actual, true))
            : static::addSuccess();
    }

    public static function throw(string $exceptionType, \Closure $test): void
    {
        try {
            $test();
            static::addFailure(sprintf(static::EXPECTED_ACTUAL_TEMPLATE, 'throw', $exceptionType, 'null'));
        } catch (\Exception $exception) {
            $exception instanceof $exceptionType
                ? static::addSuccess()
                : static::addFailure(sprintf(static::EXPECTED_ACTUAL_TEMPLATE, 'throw', $exceptionType, get_class($exception)));
        }
    }

    public static function getSourceResults(string $source): array
    {
        return array_filter(static::$assertsResults, fn($result) => $result['source'] == $source);
    }

    public static function getSourceFailResults(string $source): array
    {
        return array_filter(static::$assertsResults, fn($result) => $result['source'] == $source && !$result['right']);
    }

    public static function getResultMessage(): string
    {
        $asserts = 0;
        $success = 0;
        $fail = 0;
        foreach (static::$assertsResults as $result) {
            $asserts++;
            $result['right']
                ? $success++
                : $fail++;
        }

        return "<s style='blue'>Asserts</s>: {$asserts}\t <s style='green'>Success</s>: {$success}\t <s style='red'>Failure</s>: {$fail}\n";
    }

    public static function isFailed(): bool
    {
        return static::$failed;
    }

    private static function prepareOutput(int|float|string|object $value): string
    {
        if (is_string($value)) $value = 'string(' . strlen($value) . ') ' . $value;
        return is_object($value) ? ('obj#' . spl_object_id($value)) : (string)$value;
    }

    public static function addFailure(string $message, ?int $line = null): void
    {
        static::$failed = true;
        static::$assertsResults[] = [
            'source' => static::$source,
            'message' => $message,
            'right' => false,
            'line' => $line ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1]['line']
        ];
    }

    private static function addSuccess(): void
    {
        static::$assertsResults[] = [
            'source' => static::$source,
            'right' => true
        ];
    }
}