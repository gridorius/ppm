<?php

namespace Terminal;
use Closure;
use Exception;

class CommandTree
{
    /** @var CommandTree[] */
    protected array $commands = [];
    protected Closure $endpoint;

    public function awaitCommand(string $command, Closure $subTree = null): CommandTree
    {
        $tree = $this->commands[$command] = new CommandTree();
        if($subTree)
            $subTree($tree);
        return $tree;
    }

    public function endPoint(Closure $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    public function step($argv): void
    {
        array_shift($argv);
        $command = $argv[0] ?? null;
        if (!key_exists($command, $this->commands)) {
            if (!empty($this->endpoint)) {
                call_user_func($this->endpoint, $argv);
                return;
            }else
                throw new Exception("Command {$command} not found");
        }

        $this->commands[$command]->step($argv);
    }
}