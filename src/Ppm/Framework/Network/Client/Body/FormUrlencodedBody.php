<?php

namespace Ppm\Framework\Network\Client\Body;

use Ppm\Framework\Network\ContentTypes;

class FormUrlencodedBody extends RequestBodyBase
{
    private array $fields;

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    public function set(string $name, string $value): static
    {
        $this->fields[$name] = $value;
        return $this;
    }

    public function buildRequestBody(): string
    {
        return http_build_query($this->fields);
    }

    public function getContentType(): string
    {
        return ContentTypes::FORM_URLENCODED;
    }

    public function toCurl($curl): void
    {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->fields);
    }
}