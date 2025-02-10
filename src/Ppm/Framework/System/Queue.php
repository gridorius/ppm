<?php

namespace Ppm\Framework\System;

use Exception;
use SysvMessageQueue;

class Queue
{
    private SysvMessageQueue $queue;

    public function __construct(string $key, int $permissions = 0655)
    {
        $this->queue = msg_get_queue(crc32($key), $permissions);
    }

    public function send(string $channel, $message)
    {
        $success = msg_send($this->queue, crc32($channel), $message, true, false, $error);
        if (!$success)
            throw new Exception($error);
    }

    public function getStatus(): array
    {
        return msg_stat_queue($this->queue);
    }

    public function getCount(): int
    {
        return $this->getStatus()['msg_qnum'];
    }

    public function receive(string $channel, bool $wait = false, int $maxMessageSize = 100000): mixed
    {
        $flags = $wait ? 0 : MSG_IPC_NOWAIT;
        $success = msg_receive($this->queue, crc32($channel), $type, $maxMessageSize, $message, true, $flags, $error);
        if (!$success) {
            if ($error == MSG_ENOMSG)
                return null;

            throw new Exception($error);
        }
        return $message;
    }

    public function close(): void
    {
        msg_remove_queue($this->queue);
    }
}