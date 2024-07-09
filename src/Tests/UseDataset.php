<?php

namespace Tests;

use Attribute;

#[Attribute]
class UseDataset
{
    private $fromMethod;

    public function __construct(callable $fromMethod)
    {
        $this->fromMethod = $fromMethod;
    }

    public function getMethod(): callable
    {
        return $this->fromMethod;
    }
}