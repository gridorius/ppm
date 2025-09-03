<?php

namespace Ppm\Framework\Stream\Async;

use Exception;
use Generator;

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
     * @param Generator[] $generators
     * @param int $microseconds
     * @return void
     * @throws Exception
     */
    public static function callGenerators(array $generators, int $microseconds = 0): void
    {
        if (empty($generators))
            return;
        $read = [];
        foreach ($generators as $key => $generator)
            $read[$key] = $generator->current();

        foreach (static::awaitContent($read, $microseconds) as $key => $stream)
            $generators[$key]->next();
    }
}