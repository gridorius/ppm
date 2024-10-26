<?php

namespace Ppm\Framework\System\Proc\Descriptors;

use Ppm\Framework\System\Proc\Descriptors;

class STDDescriptor extends DescriptorBase
{
    private int $type;

    public function __construct(int $type)
    {
        $this->type = $type;
    }

    public function getDescriptor(): mixed
    {
        switch ($this->type) {
            case Descriptors::STDOUT:
                return STDOUT;
            case Descriptors::STDERR:
                return STDERR;
            default:
                return STDIN;
        }
    }
}