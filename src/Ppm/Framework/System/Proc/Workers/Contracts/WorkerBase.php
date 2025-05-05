<?php

namespace Ppm\Framework\System\Proc\Workers\Contracts;

use Ppm\Framework\CurrentAssembly;
use Ppm\Framework\Stream\Contracts\IResourceBase;
use Ppm\Framework\Stream\Contracts\IStream;
use Ppm\Framework\Stream\DescriptorStream;
use Ppm\Framework\Stream\MessageProtocol\MessageTransportProtocol;
use Ppm\Framework\Stream\Modes;
use Ppm\Framework\Stream\ResourceStream;
use Ppm\Framework\System\Proc\CommandLauncher;
use Ppm\Framework\System\Proc\Descriptors\PipeDescriptor;
use Ppm\Framework\System\Proc\LaunchedProcess;

abstract class WorkerBase extends MessageTransportProtocol implements IResourceBase
{
    protected ?LaunchedProcess $workerProcess;

    public function __construct(IStream $input, IStream $output, ?LaunchedProcess $workerProcess = null)
    {
        $input->unblock();
        $output->unblock();
        parent::__construct($input, $output);
        $this->workerProcess = $workerProcess;
    }

    public function getProcess(): LaunchedProcess
    {
        return $this->workerProcess;
    }

    /**
     * Запускает воркер
     */
    public static function create(...$arguments): static
    {
        $process = CommandLauncher::launch(
            CurrentAssembly::getAssembly()->createCommand([static::class, 'bindWorker'], ...array_map('serialize', $arguments))
                ->setDescriptor(3, new PipeDescriptor('w'))
        );

        return new static($process->getPipe(3), $process->getPipe(0), $process);
    }

    /**
     * Создает зеркало воркера в порожденном процессе
     */
    public static function bindWorker($argv): void
    {
        $worker = new static(
            new ResourceStream(STDIN),
            new DescriptorStream(3, Modes::MODE_WRITE)
        );
        array_shift($argv);
        $worker->init(array_map('unserialize', $argv));
    }

    public function close(): void
    {
        if (!is_null($this->workerProcess)) {
            $this->workerProcess->close();
            $this->getBind()->disable();
        } else {
            exit();
        }
    }

    /**
     * Инициализация воркера в порожденном процессе
     */
    abstract public function init(array $arguments): void;
}