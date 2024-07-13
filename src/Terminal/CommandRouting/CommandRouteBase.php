<?php

namespace Terminal\CommandRouting;

use Exception;
use Terminal\OptionParser;
use Terminal\ShellStyleParser;

abstract class CommandRouteBase implements ICommandRoute
{
    private bool $beforeOptions;
    private bool $afterOptions;
    private array $required;
    private array $optional;
    private OptionParser $optionParser;
    private array $matches;
    private string $description;

    public function __construct(array $matches, string $pattern)
    {
        $this->matches = $matches;
        $this->beforeOptions = !empty($matches['before_options']);
        $this->afterOptions = !empty($matches['after_options']);
        $this->required = $this->prepareRequired($matches);
        $this->optional = $this->prepareOptional($matches);
        $this->optionParser = new OptionParser();
        $this->description = '';
    }

    public function getDescription(): string
    {
        return ShellStyleParser::style(
                "<s style='b,green'>{$this->matches['command']}</s> <s style='blue'>{$this->matches['args']}</s>"
            ) .' '. $this->description . PHP_EOL;
    }

    public function setDescription(string $description): CommandRouteBase
    {
        $this->description = $description;
        return $this;
    }

    public function setDefinedOptions(array $options): CommandRouteBase
    {
        $this->optionParser->setDefinedOptions($options);
        return $this;
    }

    public function handle($argv): void
    {
        $arguments = $argv;
        $parameters = [];
        $options = [];
        if ($this->beforeOptions)
            $options = $this->optionParser->parse($arguments);

        foreach ($this->required as $name) {
            $value = array_shift($arguments);
            if (is_null($value))
                throw new Exception("Required parameter {$name} not set");
            $parameters[$name] = $value;
        }

        foreach ($this->optional as $name) {
            $parameters[$name] = array_shift($arguments);
        }

        if ($this->afterOptions)
            $options = array_merge($options, $this->optionParser->parse($arguments));

        call_user_func($this->getHandler(), $parameters, $options);
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

    abstract protected function getHandler();
}