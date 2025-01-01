<?php

namespace Ppm\Framework\Stream\Async;

use Ppm\Framework\Stream\Contracts\IStream;

interface IBindable
{
    public function bind(IStream $stream): StreamReadActionBind;
}