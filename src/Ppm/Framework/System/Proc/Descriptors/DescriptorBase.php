<?php

namespace Ppm\Framework\System\Proc\Descriptors;

abstract class DescriptorBase
{
    abstract public function configureDescriptor(array &$descriptors): void;
}