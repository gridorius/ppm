<?php

namespace Ppm\Framework\System\Proc\Descriptors;

class PipeDescriptor extends DescriptorBase
{
    private string $mode;

    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    public function getDescriptor(): array
    {
        return ['pipe', $this->mode];
    }
}