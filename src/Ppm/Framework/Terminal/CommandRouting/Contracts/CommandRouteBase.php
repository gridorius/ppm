<?php

namespace Ppm\Framework\Terminal\CommandRouting\Contracts;

use Exception;
use Ppm\Framework\Terminal\CommandRouting\Pattern;
use Ppm\Framework\Terminal\Options\OptionParser;

abstract class CommandRouteBase
{
    private Pattern $pattern;
    private string $description;
    private OptionParser $optionParser;

    public function __construct(Pattern $pattern)
    {
        $this->optionParser = new OptionParser();
        $this->pattern = $pattern;
        $this->description = '';
    }

    public function getDescription(): string
    {
        return $this->pattern->getStyledPattern() . ' ' . $this->description . PHP_EOL;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function addDefinedOptions(array $options): static
    {
        $this->optionParser->addDefinedOptions($options);
        return $this;
    }

    public function handle(array $arguments): void
    {
        $parameters = [];
        $options = [];
        array_splice($arguments, 0, $this->pattern->getCommandLength());
        if ($this->pattern->isBefore())
            $options = $this->optionParser->parse($arguments);

        foreach ($this->pattern->getRequired() as $name) {
            $value = array_shift($arguments);
            if (is_null($value))
                throw new Exception("Required parameter {$name} not set");
            $parameters[$name] = $value;
        }

        foreach ($this->pattern->getOptional() as $name) {
            $parameters[$name] = array_shift($arguments);
        }

        if ($this->pattern->isAfter())
            $options = array_merge($options, $this->optionParser->parse($arguments));

        $this->execute($parameters, $options, $arguments);
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

    abstract protected function execute(array $parameters, array $options, array $argv): void;
}