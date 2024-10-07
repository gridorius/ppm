<?php

namespace Ppm\Framework\System\Proc;

use Closure;
use Ppm\Framework\Stream\ResourceStream;

class Worker
{
    private ResourceStream $stdin;
    private ResourceStream $stdout;
    private ResourceStream $stderr;

    public function __construct()
    {
        $this->stdin = new ResourceStream(STDIN);
        $this->stdout = new ResourceStream(STDOUT);
        $this->stderr = new ResourceStream(STDERR);
    }

    public function while(Closure $iteration, ...$args): void
    {
        while (true)
            $iteration($this, ...$args);
    }

    public static function create(): static
    {
        return new static();
    }

    public function getStdin(): ResourceStream
    {
        return $this->stdin;
    }

    public function getStdout(): ResourceStream
    {
        return $this->stdout;
    }

    public function getStderr(): ResourceStream
    {
        return $this->stderr;
    }
}