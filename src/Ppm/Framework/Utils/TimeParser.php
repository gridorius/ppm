<?php

namespace Ppm\Framework\Utils;

use Exception;

class TimeParser
{
    const PATTERN = "/(?<time>\d+)(?<type>\w)\s*/";
    const TIME_ASSOCIATIONS = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400,
        'w' => 604800
    ];

    /**
     * format: 1w 2d 5h 1m
     *
     * w - week
     *
     * d - day
     *
     * h - hour
     *
     * m - minute
     *
     * s - second
     *
     * @param string $timeString
     * @return int
     * @throws Exception
     */

    public static function parse(string $timeString): int
    {
        $time = 0;
        preg_match_all(static::PATTERN, $timeString, $matches);

        foreach ($matches['type'] as $key => $type) {
            if (!key_exists($type, static::TIME_ASSOCIATIONS))
                throw new Exception("Invalid symbol {$type}");

            $time += static::TIME_ASSOCIATIONS[$type] * $matches['time'][$key];
        }

        return $time;
    }

    public static function fromSeconds(int $time): string
    {
        $reversed = array_reverse(static::TIME_ASSOCIATIONS, true);
        $remainder = $time;
        $chains = [];
        foreach ($reversed as $key => $value) {
            $amount = floor($remainder / $value);
            if ($amount >= 1) {
                $chains[] = floor($amount) . $key;
                $remainder -= $value * $amount;
            }
            if ($remainder == 0)
                break;
        }
        return implode('', $chains);
    }
    
    public static function createTimestamp(string $timeString): int
    {
        return time() + static::parse($timeString);
    }
}