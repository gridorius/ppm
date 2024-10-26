<?php

namespace Ppm\Framework\Stream\Async\Contracts;

use Ppm\Framework\Stream\IStream;

interface IStreamReceiver
{
    public function getTarget(): IStream;

    public function onReadyContent(): void;
}