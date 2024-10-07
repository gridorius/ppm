<?php

namespace Ppm\Framework\Event;

abstract class EventBase implements IEvent
{
    public function getName(): string
    {
        return get_class($this);
    }
}