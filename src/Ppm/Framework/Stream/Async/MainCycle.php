<?php

namespace Ppm\Framework\Stream\Async;

use Generator;

class MainCycle
{
    protected static AsyncStreamWatcher $watcher;
    /**
     * @var Generator[]
     */
    protected static array $queue = [];
    private static bool $active = true;
    private static int $sleepingTime = 1000;

    public static function getWatcher(): AsyncStreamWatcher
    {
        if (!isset(static::$watcher))
            static::$watcher = new AsyncStreamWatcher();

        return static::$watcher;
    }

    public static function addCoroutine($action): void
    {
        if (is_callable($action)) {
            $generator = call_user_func($action);
            if (!$generator instanceof Generator)
                throw new \Exception("Invalid coroutine");
        } else if ($action instanceof Generator) {
            $generator = $action;
        } else {
            throw new \Exception("Invalid coroutine");
        }
        static::$queue[] = $generator;
    }

    public static function run(int $microseconds): void
    {
        while (static::$active) {
            foreach (static::$queue as $key => $task) {
                $task->next();
                if (!$task->valid())
                    unset(static::$queue[$key]);
            }
            static::getWatcher()->watch($microseconds);
            usleep(static::$sleepingTime);
        }
    }

    public static function setSleepingTime(int $microseconds): void
    {
        static::$sleepingTime = $microseconds;
    }

    public static function shutdown(): void
    {
        static::$active = false;
    }
}

MainCycle::addCoroutine(function () {
    yield 1;
});