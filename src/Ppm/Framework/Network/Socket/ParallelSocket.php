<?php

namespace Ppm\Framework\Network\Socket;

use Ppm\Framework\Stream\Parallel\ParallelStreamsReader;
use Ppm\Framework\Stream\ResourceStream;

class ParallelSocket extends ParallelStreamsReader
{
    private array $changed;

    public function __construct(array $receivers = [])
    {
        parent::__construct($receivers);
        $this->changed = [];
    }

    public function clear(): static
    {
        $this->changed = [];
        return parent::clear();
    }

    public function setReceivers(array $streams): static
    {
        $this->changed = [];
        return parent::setReceivers($streams);
    }

    public function getChanged(): array
    {
        return $this->changed;
    }

    protected function read($key, ResourceStream $stream): bool
    {
        $this->changed[] = $key;
        return true;
    }
}