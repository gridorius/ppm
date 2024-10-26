<?php

namespace Ppm\Framework\Stream\Async;

use Exception;

class SelectUtils
{
    public static function awaitContent(array $readStreams, int $seconds = 1, int $microseconds = 0): array
    {
        $write = $except = null;
        $read = [];
        foreach ($readStreams as $key => $stream)
            $read[$key] = $stream->getResource();

        if (stream_select($read, $write, $except, $seconds, $microseconds) === false)
            throw new Exception('Error on stream_select');
        return $read;
    }

    public static function handleReceivers(array $receivers, int $seconds = 1, int $microseconds = 0): void
    {
        if (empty($receivers))
            return;
        $read = [];
        foreach ($receivers as $key => $receiver)
            $read[$key] = $receiver->getTarget();

        foreach (static::awaitContent($read, $seconds, $microseconds) as $key => $stream)
            $receivers[$key]->onReadyContent();
    }
}