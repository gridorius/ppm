<?php

namespace Ppm\Framework\Event;

interface IEvent
{
    public function cancel(): void;

    public function isCancelled(): bool;
}