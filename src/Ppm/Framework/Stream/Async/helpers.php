<?php

function timeCoroutine(int $time, callable $callback): Generator
{
    $generator = call_user_func($callback);
    if (!$generator instanceof Generator)
        throw new Exception("Invalid coroutine");
    $end = time() + $time;
    while (time() < $end)
        yield;

    foreach ($generator as $value)
        yield $value;
}