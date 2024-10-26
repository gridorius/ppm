<?php

namespace Ppm\Framework\System\Proc\Workers;

class WorkerManager
{
    /**
     * @var WorkerBase[]
     */
    protected array $workers;

    public function __construct(array $workers = [])
    {
        $this->workers = $workers;
    }

    public function addWorker(WorkerBase $worker): static
    {
        $this->workers[] = $worker;
        return $this;
    }

    public function write(string $data): static
    {
        foreach ($this->workers as $worker)
            $worker->getOutput()->write($data);
        return $this;
    }

    public function close(): void
    {
        foreach ($this->workers as $worker)
            $worker->close();
    }
}