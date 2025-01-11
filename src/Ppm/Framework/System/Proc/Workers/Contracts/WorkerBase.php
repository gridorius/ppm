<?php

namespace Ppm\Framework\System\Proc\Workers\Contracts;

use Ppm\Framework\CurrentAssembly;
use Ppm\Framework\Stream\Async\StreamReadActionBind;
use Ppm\Framework\Stream\Contracts\IResourceBase;
use Ppm\Framework\Stream\Contracts\IStream;
use Ppm\Framework\Stream\DescriptorStream;
use Ppm\Framework\Stream\Modes;
use Ppm\Framework\Stream\ResourceStream;
use Ppm\Framework\Stream\StreamMessageProtocol;
use Ppm\Framework\System\Proc\CommandLauncher;
use Ppm\Framework\System\Proc\Descriptors\PipeDescriptor;
use Ppm\Framework\System\Proc\LaunchedProcess;

abstract class WorkerBase implements IWorker, IResourceBase
{
    protected array $handlers;
    protected array $partyHandlers;
    protected IStream $input;
    protected IStream $output;
    protected StreamReadActionBind $bind;
    protected StreamMessageProtocol $protocol;

    protected ?LaunchedProcess $workerProcess;

    public function __construct(IStream $input, IStream $output, LaunchedProcess $workerProcess = null)
    {
        $this->handlers = [];
        $this->partyHandlers = [];
        $this->input = $input;
        $this->output = $output;
        $this->bind = StreamReadActionBind::create($this->input, function (IStream $stream) {
            $this->onReadyData($stream);
        });
        $this->protocol = new StreamMessageProtocol();
        $this->workerProcess = $workerProcess;
    }

    /**
     * Запускает воркер
     */
    public static function create(): static
    {
        $process = CommandLauncher::launch(
            CurrentAssembly::getAssembly()->createCommand([static::class, 'bindWorker'])
                ->setDescriptor(3, new PipeDescriptor('w'))
        );

        return new static($process->getPipe(3), $process->getPipe(0), $process);
    }

    /**
     * Создает зеркало воркера в порожденном процессе
     */
    public static function bindWorker(): void
    {
        $worker = new static(
            new ResourceStream(STDIN),
            new DescriptorStream(3, Modes::MODE_WRITE)
        );
        $worker->init();
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
        $this->output->write(StreamMessageProtocol::prepareMessage($message, $headers));
    }

    protected function onReadyData(IStream $stream): void
    {
        $this->protocol->onReadyData($stream, function (string $message, array $headers, int $remainderLength) {
            $this->onReadyParty($message, $headers, $remainderLength);
        });
        if ($this->protocol->isCompleted()) {
            $this->callMessageHandlers();
            $this->protocol->reset();
        }
    }

    protected function onReadyParty(string $message, array $headers, int $remainderLength): void
    {
        foreach ($this->partyHandlers as $handler)
            call_user_func($handler, $message, $headers, $remainderLength);
    }

    protected function callMessageHandlers(): void
    {
        foreach ($this->handlers as $handler)
            call_user_func($handler, $this->protocol->getMessage(), $this->protocol->getHeaders());
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
    abstract public function init(): void;
}