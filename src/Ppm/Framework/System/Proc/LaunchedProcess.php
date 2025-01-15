<?php

namespace Ppm\Framework\System\Proc;

use Ppm\Framework\Stream\ResourceStream;

class LaunchedProcess extends ProcResource
{
    private array $pipes;
    private CommandConfiguration $commandConfiguration;

    public function __construct($process, array $pipes, CommandConfiguration $commandConfiguration)
    {
        parent::__construct($process);
        $this->commandConfiguration = $commandConfiguration;
        $this->pipes = array_map([ResourceStream::class, 'from'], $pipes);
    }

    public function getPipe(int $index): ?ResourceStream
    {
        return $this->pipes[$index];
    }

    public function getCommandConfiguration(): CommandConfiguration
    {
        return $this->commandConfiguration;
    }

    public function isRunning(): ?bool
    {
        return $this->getStatus('running');
    }

    public function close(): void
    {
        foreach ($this->pipes as $pipe)
            $pipe->close();
        parent::close();
    }

    public function isClosed(): bool
    {
        return !$this->isRunning();
    }
}