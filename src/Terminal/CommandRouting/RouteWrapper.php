<?php

namespace Terminal\CommandRouting;

class RouteWrapper
{
    private CommandRouteBase $commandRouteBase;
    private int $removeArgs;

    /**
     * @param CommandRouteBase $commandRouteBase
     * @param int $removeArgs
     */
    public function __construct(CommandRouteBase $commandRouteBase, int $removeArgs)
    {
        $this->commandRouteBase = $commandRouteBase;
        $this->removeArgs = $removeArgs;
    }

    public function getHandler(): CommandRouteBase
    {
        return $this->commandRouteBase;
    }

    public function handle(array $argv): void
    {
        array_splice($argv, 0, $this->removeArgs);
        $this->commandRouteBase->handle($argv);
    }
}