<?php

namespace Ppm\Framework\System\Proc;

use Ppm\Framework\Stream\ResourceStream;

class LaunchedProcess extends ProcResource
{
    private array $pipes;
    private bool $depend;

    public function __construct($process, array $pipes, bool $depend = true)
    {
        parent::__construct($process);
        $this->depend = $depend;
        $this->pipes = array_map([ResourceStream::class, 'from'], $pipes);
    }

    public function isDepend(): bool
    {
        return $this->depend;
    }

    public function getPipe(int $index): ?ResourceStream
    {
        return $this->pipes[$index];
    }

    public function getPipes(): array
    {
        return $this->pipes;
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