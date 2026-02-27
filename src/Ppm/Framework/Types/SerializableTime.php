<?php

namespace Ppm\Framework\Types;

use JsonSerializable;

class SerializableTime extends DateTimeFrom implements JsonSerializable
{
    public function jsonSerialize(): string
    {
        return $this->format('H:i:s');
    }
}