<?php

namespace Ppm\Framework\Stream\Async;

use Closure;

class Promise
{
    const STATE_PENDING = 0;
    const STATE_FULFILLED = 1;
    const STATE_REJECTED = 2;
    private array $_onFulfilled = [];
    private array $_onRejected = [];
    private int $state = self::STATE_PENDING;
    private $result;

    public function __construct(callable $callback)
    {
        call_user_func($callback, function ($result) {
            $this->state = self::STATE_FULFILLED;
            $this->result = $result;
            $this->callState();
        }, function ($reason) {
            $this->state = self::STATE_REJECTED;
            $this->result = $reason;
            $this->callState();
        });
    }

    public static function resolve($result): static
    {
        return new Promise(function ($resolve) use ($result) {
            $resolve($result);
        });
    }

    public static function reject($error = null): static
    {
        return new Promise(function ($resolve, $reject) use ($error) {
            $reject($error);
        });
    }

    public function then(callable $onFulfilled): static
    {
        $promise = new Promise(function (callable $resolve, callable $reject) use ($onFulfilled) {
            $this->_onFulfilled[] = function () use ($resolve, $reject, $onFulfilled) {
                $result = call_user_func($onFulfilled, $this->result);
                if ($result instanceof Promise)
                    $result->then(function ($result) use ($resolve) {
                        $resolve($result);
                    });
                else
                    $resolve($result);
            };
            $this->_onRejected[] = function () use ($resolve, $reject) {
                $reject($this->result);
            };
        });
        $this->callState();
        return $promise;
    }

    public function catch(callable $onRejected): static
    {
        $promise = new Promise(function (callable $resolve, callable $reject) use ($onRejected) {
            $this->_onFulfilled[] = function () use ($resolve, $reject) {
                $resolve($this->result);
            };
            $this->_onRejected[] = function () use ($resolve, $reject, $onRejected) {
                $reject(call_user_func($onRejected, $this->result));
            };
        });
        $this->callState();
        return $promise;
    }

    private function callState(): void
    {
        if ($this->state == self::STATE_FULFILLED)
            while (!is_null($callback = array_shift($this->_onFulfilled)))
                call_user_func($callback, $this->result);

        if ($this->state == self::STATE_REJECTED)
            while (!is_null($callback = array_shift($this->_onRejected)))
                call_user_func($callback, $this->result);
    }
}