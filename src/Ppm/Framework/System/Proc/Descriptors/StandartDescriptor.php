<?php

namespace Ppm\Framework\System\Proc\Descriptors;

use Ppm\Framework\System\Proc\Descriptors;

class StandartDescriptor extends DescriptorBase
{
    private int $type;

    public function __construct(int $type)
    {
        $this->type = $type;
    }

    public function configureDescriptor(array &$descriptors): void
    {
        switch ($this->type) {
            case Descriptors::STDOUT:
                $descriptors[] = STDOUT;
                break;
            case Descriptors::STDERR:
                $descriptors[] = STDERR;
                break;
            default:
                $descriptors[] = STDIN;
        }
    }
}