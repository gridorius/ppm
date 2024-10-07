<?php

namespace Ppm\Framework\Stream;

abstract class ResourceBase
{
    abstract public function close(): void;
}