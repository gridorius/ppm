<?php

namespace Ppm\Framework\System\Proc\Descriptors;

class FileDescriptor extends DescriptorBase
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function configureDescriptor(array &$descriptors): void
    {
        $descriptors[] = ['file', $this->path, 'a'];
    }
}