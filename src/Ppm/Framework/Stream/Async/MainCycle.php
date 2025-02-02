<?php

namespace Ppm\Framework\Stream\Async;

class MainCycle
{
    protected static AsyncStreamWatcher $watcher;
    /**
     * @var Task[]
     */
    protected static array $queue = [];
    private static bool $active = true;

    public static function getWatcher(): AsyncStreamWatcher
    {
        if (!isset(static::$watcher))
            static::$watcher = new AsyncStreamWatcher();

        return static::$watcher;
    }

    public static function addTask(Task $action): void
    {
        static::$queue[] = $action;
    }

    public static function run(int $seconds, int $microseconds): void
    {
        while (static::$active) {
            foreach (static::$queue as $key => $task) {
                if ($task->isRunning()) {
                    $task->run();
                    if (!$task->isEveryTime())
                        unset(static::$queue[$key]);
                }
            }
            static::getWatcher()->watch($seconds, $microseconds);
        }
    }

    public static function shutdown(): void
    {
        static::$active = false;
    }
}