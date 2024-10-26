<?php

namespace Ppm\Framework\Network\Client\Body;


use Ppm\Framework\Network\Constants\HttpContentTypes;

class RawBody extends RequestBodyBase
{
    private string $body;
    private string $contentType;

    public function __construct(string $body, string $contentType = HttpContentTypes::TEXT)
    {
        $this->body = $body;
        $this->contentType = $contentType;
    }

    public function buildRequestBody(): string
    {
        return $this->body;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function toCurl($curl): void
    {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->body);
    }
}