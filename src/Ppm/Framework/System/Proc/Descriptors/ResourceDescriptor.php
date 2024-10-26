<?php

namespace Ppm\Framework\System\Proc\Descriptors;

use Ppm\Framework\Resources\Resource;

class ResourceDescriptor extends DescriptorBase
{
    public $resource;

    public function __construct(resource $resource)
    {
        $this->resource = $resource;
    }

    public function getDescriptor(): resource
    {
        return $this->resource;
    }
}