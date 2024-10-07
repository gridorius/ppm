<?php

namespace Ppm\Framework\Terminal\CommandRouting;

use Ppm\Framework\Terminal\Options\OptionParser;
use Ppm\Framework\Terminal\ShellStyleParser;

class Pattern
{
    const COMMAND_PATTERN = "/^(?<command>[^-\[\]<>]+)\s*(?<args>(?<before_options>-)?\s*"
    . "(?<required>(<.+?>\s*)+)?(?<optional>(\[.+?\]\s*)+)?(?<after_options>-)?)/";
    private bool $before;
    private bool $after;
    private array $required;
    private array $optional;
    private string $command;
    private string $args;

    public function __construct(string $pattern)
    {
        preg_match(static::COMMAND_PATTERN, $pattern, $matches);
        if ($matches[0] != $pattern)
            throw new Exception("Invalid command pattern: {$pattern}");

        $this->command = trim($matches['command']);
        $this->args = trim($matches['args']);
        $this->before = !empty($matches['before_options']);
        $this->after = !empty($matches['after_options']);
        $this->required = $this->prepareRequired($matches);
        $this->optional = $this->prepareOptional($matches);
    }

    public function getRoute(): string
    {
        $command = $this->command;
        return "/^{$command}/";
    }

    public function isBefore(): bool
    {
        return $this->before;
    }

    public function isAfter(): bool
    {
        return $this->after;
    }

    public function getRequired(): array
    {
        return $this->required;
    }

    public function getOptional(): array
    {
        return $this->optional;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getArgs(): string
    {
        return $this->args;
    }

    public function getStyledPattern(): string
    {
        $command = trim($this->command);
        $args = trim($this->args);
        return ShellStyleParser::style(
            "    <s style='b,green'>{$command}</s> <s style='blue'>{$args}</s>"
        );
    }

    private function prepareRequired(array $matches): array
    {
        return !empty($matches['required'])
            ? array_map(function ($required) {
                return trim($required, '<> ');
            }, preg_split("/\s+/", trim($matches['required'])))
            : [];
    }

    private function prepareOptional(array $matches): array
    {
        return !empty($matches['optional'])
            ? array_map(function ($required) {
                return trim($required, '[] ');
            }, preg_split("/\s+/", trim($matches['optional'])))
            : [];
    }
}