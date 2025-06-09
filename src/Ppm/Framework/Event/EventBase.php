<?php

namespace Ppm\Framework\Event;

abstract class EventBase implements IEvent
{
    private bool $_cancelled = false;

    public function cancel(): void
    {
        $this->_cancelled = true;
    }

    public function isCancelled(): bool
    {
        return $this->_cancelled;
    }

    public function getName(): string
    {
        return get_class($this);
    }
}