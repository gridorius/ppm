<?php

namespace Ppm\Framework\System\Proc\Workers;

use Ppm\Framework\Network\ClientDisconnectedException;
use Ppm\Framework\Stream\Async\AsyncStreamWatcher;
use Ppm\Framework\Stream\Async\MainCycle;
use Ppm\Framework\Stream\Async\Promise;
use Ppm\Framework\Stream\Contracts\IStream;
use Ppm\Framework\Stream\MessageProtocol\MessageReceiver;
use Ppm\Framework\System\Proc\LaunchedProcess;

class CommandWorker extends ObjectTransferWorker
{
    private array $promiseHandlers = [];

    public function __construct(IStream $input, IStream $output, ?LaunchedProcess $workerProcess = null)
    {
        parent::__construct($input, $output, $workerProcess);
        $this->onMessage(function (MessageReceiver $protocol, $result) {
            if ($protocol->getHeader('type') == 'response'
                && !empty($handler = $this->promiseHandlers[$protocol->getHeader('id')]))
                call_user_func($handler, $result);
        });
    }

    public function init(array $arguments): void
    {
        $this->onMessage(function (MessageReceiver $protocol, $data) {
            switch ($protocol->getHeader('type')) {
                case 'include':
                    include $protocol->getHeader('path');
                    break;
                case 'execute':
                    $result = call_user_func_array($data, $protocol->getHeader('arguments'));
                    $this->sendData($result, [
                        'type' => 'response',
                        'id' => $protocol->getHeader('id')
                    ]);
            }
        });

        MainCycle::watch((function () {
            yield $this->input;
            while (!$this->receiver->isAborted())
                $this->receiver->onReadyData($this->input);
        })());
    }

    public function execute($handler, ...$arguments): Promise
    {
        $id = uniqid('correlation_', true);
        $this->sendData($handler, [
            'type' => 'execute',
            'arguments' => $arguments,
            'id' => $id
        ]);
        return new Promise(function ($resolve) use ($id) {
            $this->promiseHandlers[$id] = $resolve;
        });
    }

    public function include(string $path): void
    {
        $this->sendHeaders([
            'type' => 'include',
            'path' => $path
        ]);
    }
}