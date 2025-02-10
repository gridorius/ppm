<?php

namespace Ppm\Framework\Stream\Async;

use Exception;

class SelectUtils
{
    public static function awaitContent(array $readStreams, int $microseconds = 0): array
    {
        $write = $except = null;
        $read = [];
        foreach ($readStreams as $key => $stream)
            $read[$key] = $stream->getResource();

        if (stream_select($read, $write, $except, 0, $microseconds) === false)
            throw new Exception('Error on stream_select');
        return $read;
    }

    /**
     * @param StreamReadActionBind[] $streamBindings
     * @param int $seconds
     * @param int $microseconds
     * @return void
     * @throws Exception
     */
    public static function callBindings(array $streamBindings, int $microseconds = 0): void
    {
        if (empty($streamBindings))
            return;
        $read = [];
        foreach ($streamBindings as $key => $binding)
            $read[$key] = $binding->getStream();

        foreach (static::awaitContent($read, $microseconds) as $key => $stream)
            $streamBindings[$key]->call();
    }
}