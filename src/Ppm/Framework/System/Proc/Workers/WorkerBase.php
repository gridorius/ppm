<?php

namespace Ppm\Framework\System\Proc\Workers;

use Ppm\Framework\CurrentAssembly;
use Ppm\Framework\Stream\DescriptorStream;
use Ppm\Framework\Stream\FileResourceStream;
use Ppm\Framework\Stream\Modes;
use Ppm\Framework\Stream\ResourceStream;
use Ppm\Framework\System\Proc\CommandLauncher;
use Ppm\Framework\System\Proc\Descriptors\PipeDescriptor;

abstract class WorkerBase
{
    protected ResourceStream $output;
    protected ResourceStream $input;

    public function __construct(ResourceStream $input, ResourceStream $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function getInput(): ResourceStream
    {
        return $this->input;
    }

    public function getOutput(): ResourceStream
    {
        return $this->output;
    }

    public static function start(array $argv): void
    {
        $worker = new static(
            new ResourceStream(STDIN),
            new DescriptorStream(3, Modes::MODE_WRITE)
        );

        $worker->run($argv);
    }

    public static function create(...$arguments): static
    {
        $worker = CommandLauncher::launch(
            CurrentAssembly::getAssembly()->createCommand([static::class, 'start'], ...$arguments)
                ->setDescriptor(3, new PipeDescriptor('w'))
        );
        return new static($worker->getPipe(3), $worker->getPipe(0));
    }

    abstract public function run(array $argv);
}