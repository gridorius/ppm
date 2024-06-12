<?php

namespace Terminal\CommandRouting;

use Closure;
use Exception;

class CommandsRouter
{
    private array $commands = [];

    private string $commandPattern = "/^(?<command>[^-\[\]<>]+)\s*(?<before_options>-)?\s*"
    . "(?<required>(<.+?>\s*)+)?(?<optional>(\[.+?\]\s*)+)?(?<after_options>-)?/";

    private CommandRouteBase $notFoundHandler;

    public function __construct()
    {
        $this->notFoundHandler = (new CommandRouteClosure([], ''))->setHandler(function () {
            echo "Command not found\n";
            exit(1);
        });
    }

    public function setNotFoundHandler(CommandRouteBase $commandRouteBase): void
    {
        $this->notFoundHandler = $commandRouteBase;
    }

    public function register(string $pattern, Closure $handler): CommandRouteBase
    {
        preg_match($this->commandPattern, $pattern, $matches);
        if ($matches[0] != $pattern)
            throw new Exception("Invalid command pattern: {$pattern}");

        $command = trim($matches['command']);
        $pattern = "/^{$command}/";

        $handler = (new CommandRouteClosure($matches, $pattern))->setHandler($handler);
        $this->commands[$pattern] = new RouteWrapper($handler, count(explode(' ', $command)));
        return $handler;
    }

    public function registerCommand(string $pattern, CommandBase $concreteCommand): CommandRouteBase
    {
        preg_match($this->commandPattern, $pattern, $matches);
        if ($matches[0] != $pattern)
            throw new Exception("Invalid command pattern: {$pattern}");


        $command = trim($matches['command']);
        $pattern = "/^{$command}/";
        $handler = (new CommandRouteCommand($matches, $pattern))
            ->setHandler($concreteCommand)
            ->setDefinedOptions($concreteCommand->getOptions());
        $this->commands[$pattern] = new RouteWrapper($handler, count(explode(' ', $command)));
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
}