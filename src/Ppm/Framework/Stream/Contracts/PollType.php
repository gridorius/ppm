<?php

namespace Ppm\Framework\Stream\Contracts;

enum PollType
{
    case Read;
    case Write;
}