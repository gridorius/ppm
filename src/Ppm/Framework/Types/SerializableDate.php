<?php

namespace Ppm\Framework\Types;

use JsonSerializable;

class SerializableDate extends DateTimeFrom implements JsonSerializable
{
    public function jsonSerialize(): string
    {
        return $this->format('Y-m-d');
    }
}