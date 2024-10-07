<?php

namespace Ppm\Framework\System\Proc;

use Ppm\Framework\Stream\ResourceStream;

class LaunchedProcess extends ProcResource
{
    private array $pipes;

    public function __construct($process, array $pipes)
    {
        parent::__construct($process);
        $this->pipes = array_map([ProcResource::class, 'from'], $pipes);
    }

    public function getPipe(int $index): ?ResourceStream
    {
        return $this->pipes[$index];
    }

    public function isRunning(): ?bool
    {
        return $this->getStatus('running');
    }

    public function isClosed(): bool
    {
        return !$this->isRunning();
    }
}