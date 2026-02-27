<?php

namespace Ppm\Framework\System\Proc;


use Ppm\Framework\Stream\Contracts\IResourceBase;

class ProcResource implements IResourceBase
{
    private $resource;
    private int $pid;

    public function __construct($resource)
    {
        $this->resource = $resource;
        $this->pid = $this->getStatus('pid');
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function close(): void
    {
        proc_close($this->resource);
    }

    function getStatus($key = null)
    {
        $status = proc_get_status($this->resource);
        return $key ? $status[$key] : $status;
    }
}