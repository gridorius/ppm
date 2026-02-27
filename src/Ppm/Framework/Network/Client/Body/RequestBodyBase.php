<?php

namespace Ppm\Framework\Network\Client\Body;

abstract class RequestBodyBase
{
    abstract public function buildRequestBody(): string;

    abstract public function getContentType(): string;

    abstract public function toCurl($curl): void;
}