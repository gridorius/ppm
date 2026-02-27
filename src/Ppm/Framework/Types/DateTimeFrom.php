<?php

namespace Ppm\Framework\Types;

use DateTime;

class DateTimeFrom extends DateTime
{
    public static function from(DateTime $dateTime): static
    {
        return new static($dateTime->format('Y-m-d H:i:s'));
    }
}