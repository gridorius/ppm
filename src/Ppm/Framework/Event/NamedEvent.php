<?php

namespace Ppm\Framework\Event;

class NamedEvent extends EventBase
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return static::getEventName($this->name);
    }

    public static function getEventName(string $name): string
    {
        return static::class . '_' . $name;
    }
}