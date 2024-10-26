<?php

namespace Ppm\Framework\Stream\Async\Contracts;

interface IStateReceiver extends IStreamReceiver
{
    public function isReady(): bool;
}