<?php

namespace Ppm\Framework\Stream\Parallel;

use Exception;

class ParallelStreamsReader
{
    /**
     * @var StreamReceiverBase[]
     */
    private array $receivers;

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

    public function handleInput(int $seconds = 1, int $microseconds = 0): void
    {
        $write = $except = null;
        $read = [];
        while (count($this->receivers) > 0) {
            foreach ($this->receivers as $key => $receiver)
                $read[$key] = $receiver->getStream()->getResource();
            if (stream_select($read, $write, $except, $seconds, $microseconds) === false)
                throw new Exception('Error on stream_select');
            foreach ($read as $key => $stream)
                if (!$this->receivers[$key]->onReadyContent())
                    unlink($this->receivers[$key]);
            $read = [];
        }
    }

    public function clear(): static
    {
        $this->receivers = [];
        return $this;
    }
}