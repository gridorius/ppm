<?php

namespace Ppm\Framework\Stream\Async;

use Ppm\Framework\Stream\Async\Contracts\IStateReceiver;

abstract class ReceiverStateCollectionBase
{
    /**
     * @var IStateReceiver[]
     */
    protected array $receivers;

    public function __construct()
    {
        $this->receivers = [];
    }

    public function setReceiver(string $key, IStateReceiver $receiver): static
    {
        $this->receivers[$key] = $receiver;
        return $this;
    }

    public function getReceivers(): array
    {
        return $this->receivers;
    }

    public function size(): int
    {
        return count($this->receivers);
    }

    public function getUnReadyReceivers(): array
    {
        return array_filter($this->receivers, function (IStateReceiver $receiver) {
            return !$receiver->isReady();
        });
    }

    public function getReadyKey(): ?string
    {
        foreach ($this->receivers as $key => $receiver)
            if ($receiver->isReady())
                return $key;

        return null;
    }


    public function hasReady(): bool
    {
        foreach ($this->receivers as $receiver)
            if ($receiver->isReady())
                return true;

        return false;
    }

    abstract public function getReady(): ?IStateReceiver;

    abstract public function shiftReady(): ?IStateReceiver;
}
