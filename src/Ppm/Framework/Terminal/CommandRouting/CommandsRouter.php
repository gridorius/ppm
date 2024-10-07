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
     * @var RouteWrapper[] $commands
     */
    private array $commands = [];

    private CommandRouteBase $notFoundHandler;
    private string $descriptionHeader = '';

    public function __construct()
    {
        $this->notFoundHandler = new CommandRouteClosure([], '', function () {
            $this->showDescription();
        });
    }

    public function setDescriptionHeader(string $descriptionHeader): void
    {
        $this->descriptionHeader = ShellStyleParser::style("<s style='b,green'>{$descriptionHeader}</s>");
    }

    public function showDescription(): void
    {
        echo $this->descriptionHeader . PHP_EOL;
        foreach ($this->commands as $command) {
            $handler = $command->getHandler();
            echo $handler->getDescription();
        }
    }

    public function setNotFoundHandler(CommandRouteBase $commandRouteBase): void
    {
        $this->notFoundHandler = $commandRouteBase;
    }

    public function register(string $patternString, Closure $handler): CommandRouteBase
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

        $this->notFoundHandler->handle($argv);
    }

    private function registerRoute(Pattern $pattern, CommandRouteBase $handler): void
    {
        $this->commands[$pattern->getRoute()] = $handler;
    }
}