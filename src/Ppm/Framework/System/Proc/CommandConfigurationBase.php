<?php

namespace Ppm\Framework\System\Proc;

use Ppm\Framework\System\Proc\Descriptors\DescriptorBase;
use Ppm\Framework\System\Proc\Descriptors\PipeDescriptor;
use Ppm\Framework\System\Proc\Descriptors\StandartDescriptor;

abstract class CommandConfigurationBase
{
    /**
     * @var DescriptorBase[]
     */
    protected array $descriptors;
    public function __construct()
    {
        $this->descriptors = [];
        $this
            ->setDescriptor(Descriptors::STDIN, new PipeDescriptor('r'))
            ->setDescriptor(Descriptors::STDOUT, new StandartDescriptor(Descriptors::STDOUT))
            ->setDescriptor(Descriptors::STDERR, new StandartDescriptor(Descriptors::STDERR));
    }

    public function setDescriptor(int $descriptor, DescriptorBase $type): self
    {
        $this->descriptors[$descriptor] = $type;
        return $this;
    }

    public function configureDescriptors(array &$descriptors): void
    {
        foreach ($this->descriptors as $descriptor)
            $descriptor->configureDescriptor($descriptors);
    }
}