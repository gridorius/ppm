<?php

namespace Ppm\Framework\Stream\Async;

use Ppm\Framework\Stream\Async\Contracts\StreamReceiverBase;

class AsyncStreamsReader
{
    /**
     * @var StreamReceiverBase[]
     */
    protected array $receivers;

    public function __construct(array $receivers = [])
    {
        $this->receivers = $receivers;
    }

    public function addReceiver(StreamReceiverBase $stream): static
    {
        $this->receivers[] = $stream;
        return $this;
    }

    public function setReceivers(array $receivers): static
    {
        $this->receivers = $receivers;
        return $this;
    }

    public function getReceivers(): array
    {
        return $this->receivers;
    }

    public function awaitContent(int $seconds = 1, int $microseconds = 0): void
    {
        SelectUtils::handleReceivers($this->receivers, $seconds, $microseconds);
    }

    public function clear(): static
    {
        $this->receivers = [];
        return $this;
    }
}