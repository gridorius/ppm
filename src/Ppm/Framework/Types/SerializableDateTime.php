<?php

namespace Ppm\Framework\Types;

use JsonSerializable;

class SerializableDateTime extends DateTimeFrom implements JsonSerializable
{
    public function jsonSerialize(): string
    {
        return $this->format('Y-m-d H:i:s');
    }
}