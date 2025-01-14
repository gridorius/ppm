<?php

namespace Ppm\Framework\System\Proc\Workers\Contracts;

use Ppm\Framework\CurrentAssembly;
use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\Contracts\IResourceBase;
use Ppm\Framework\Stream\Contracts\IStream;
use Ppm\Framework\Stream\DescriptorStream;
use Ppm\Framework\Stream\MessageProtocol\StreamMessageProtocol;
use Ppm\Framework\Stream\MessageProtocol\StreamMessageProtocolWrapper;
use Ppm\Framework\Stream\Modes;
use Ppm\Framework\Stream\ResourceStream;
use Ppm\Framework\System\Proc\CommandLauncher;
use Ppm\Framework\System\Proc\Descriptors\PipeDescriptor;
use Ppm\Framework\System\Proc\LaunchedProcess;

abstract class WorkerBase implements IWorker, IResourceBase
{
    protected array $handlers;
    protected array $partyHandlers;
    protected IStream $input;
    protected IStream $output;
    protected StreamMessageProtocolWrapper $outputWrapper;
    protected StreamReadActionBind $bind;
    protected StreamMessageProtocol $protocol;

    protected ?LaunchedProcess $workerProcess;

    public function __construct(IStream $input, IStream $output, LaunchedProcess $workerProcess = null)
    {
        $input->unblock();
        $output->unblock();
        $this->handlers = [];
        $this->partyHandlers = [];
        $this->input = $input;
        $this->output = $output;
        $this->outputWrapper = StreamMessageProtocolWrapper::wrap($this->output);
        $this->protocol = new StreamMessageProtocol(
            function () {
                $this->callMessageHandlers();
                $this->protocol->reset();
            },
            function (StreamMessageProtocol $protocol, string $party) {
                $this->onReadyParty($protocol, $party);
            }
        );
        $this->bind = $this->protocol->createBind($this->input);
        $this->workerProcess = $workerProcess;
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
        $worker->init(...array_map('unserialize', $argv));
    }

    public function getBind(): StreamReadActionBind
    {
        return $this->bind;
    }

    public function onMessage(callable $callable): static
    {
        $this->handlers[] = $callable;
        return $this;
    }

    public function onMessageParty(callable $callable): static
    {
        $this->partyHandlers[] = $callable;
        return $this;
    }

    public function send(string $message, array $headers = []): void
    {
        $this->outputWrapper->send($message, $headers);
    }

    protected function onReadyParty(StreamMessageProtocol $protocol, string $party): void
    {
        foreach ($this->partyHandlers as $handler)
            call_user_func($handler, $protocol, $party);
    }

    protected function callMessageHandlers(): void
    {
        foreach ($this->handlers as $handler)
            call_user_func($handler, $this->protocol);
    }

    public function close(): void
    {
        if (!is_null($this->workerProcess)) {
            $this->workerProcess->close();
        } else {
            exit();
        }
    }

    /**
     * Инициализация воркера в порожденном процессе
     */
    abstract public function init(...$arguments): void;
}