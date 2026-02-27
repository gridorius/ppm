<?php

namespace Ppm\Framework\Terminal\CommandRouting;

use Closure;
use Exception;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandRouteBase;
use Ppm\Framework\Terminal\ShellStyleParser;

class CommandsRouter
{
    /**
     * @var CommandRouteBase[] $commands
     */
    private array $commands = [];

    private ?CommandRouteBase $notFoundHandler;
    private string $descriptionHeader = '';

    public function __construct()
    {
        $this->notFoundHandler = null;
    }

    public function setDescriptionHeader(string $name, string $postfix = ''): void
    {
        $this->descriptionHeader = ShellStyleParser::style("<s b green>{$name}</s> <s blue>{$postfix}</s>");
    }

    public function showDescription(): void
    {
        echo $this->descriptionHeader . PHP_EOL;
        foreach ($this->commands as $handler)
            echo $handler->getDescription();
    }

    public function setNotFoundHandler(CommandRouteBase $commandRouteBase): void
    {
        $this->notFoundHandler = $commandRouteBase;
    }

    public function register(string $patternString, callable $handler): CommandRouteBase
    {
        $pattern = new Pattern($patternString);
        $handler = new CommandRouteClosure($pattern, $handler);
        $this->registerRoute($pattern, $handler);
        return $handler;
    }

    public function registerCommand(string $patternString, CommandBase $concreteCommand): CommandRouteBase
    {
        $pattern = new Pattern($patternString);
        $handler = (new CommandRouteCommand($pattern, $concreteCommand))
            ->setDescription($concreteCommand->getDescription())
            ->addDefinedOptions($concreteCommand->getOptions());
        $this->registerRoute($pattern, $handler);
        return $handler;
    }

    public function applyConfiguration(CommandsConfigurationBase $configuration): static
    {
        $configuration->configure($this);
        return $this;
    }

    public function handle(array $argv): void
    {
        array_shift($argv);
        $fullCommand = implode(' ', $argv);
        foreach ($this->commands as $pattern => $wrapper)
            if (preg_match($pattern, $fullCommand)) {
                $wrapper->handle($argv);
                return;
            }

        if (!is_null($this->notFoundHandler))
            $this->notFoundHandler->handle($argv);
        else
            $this->showDescription();
    }

    private function registerRoute(Pattern $pattern, CommandRouteBase $handler): void
    {
        $this->commands[$pattern->getRoute()] = $handler;
    }
}