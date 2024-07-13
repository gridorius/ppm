<?php

namespace Terminal\CommandRouting;

use Closure;
use Exception;

class CommandsRouter
{
    /**
     * @var RouteWrapper[] $commands
     */
    private array $commands = [];

    private string $commandPattern = "/^(?<command>[^-\[\]<>]+)\s*(?<args>(?<before_options>-)?\s*"
    . "(?<required>(<.+?>\s*)+)?(?<optional>(\[.+?\]\s*)+)?(?<after_options>-)?)/";

    private CommandRouteBase $notFoundHandler;

    public function __construct()
    {
        $this->notFoundHandler = new CommandRouteClosure([], '', function () {
            $this->showHelp();
        });
    }

    public function showHelp(): void
    {
        foreach ($this->commands as $command) {
            $handler = $command->getHandler();
            echo $handler->getDescription();
        }
    }

    public function setNotFoundHandler(CommandRouteBase $commandRouteBase): void
    {
        $this->notFoundHandler = $commandRouteBase;
    }

    public function register(string $pattern, Closure $handler): CommandRouteBase
    {
        $matches = $this->getMatches($pattern);
        $handler = new CommandRouteClosure($matches, $pattern, $handler);
        $this->setPatternHandler($matches, $handler);
        return $handler;
    }

    public function registerCommand(string $pattern, CommandBase $concreteCommand): CommandRouteBase
    {
        $matches = $this->getMatches($pattern);
        $handler = (new CommandRouteCommand($matches, $pattern, $concreteCommand))
            ->setDescription($concreteCommand->getDescription())
            ->setDefinedOptions($concreteCommand->getOptions());
        $this->setPatternHandler($matches, $handler);
        return $handler;
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

    private function getMatches(string $pattern): array
    {
        preg_match($this->commandPattern, $pattern, $matches);
        if ($matches[0] != $pattern)
            throw new Exception("Invalid command pattern: {$pattern}");
        return $matches;
    }

    private function setPatternHandler(array $matches, CommandRouteBase $handler): void
    {
        $command = trim($matches['command']);
        $pattern = "/^{$command}/";
        $this->commands[$pattern] = new RouteWrapper($handler, count(explode(' ', $command)));
    }
}