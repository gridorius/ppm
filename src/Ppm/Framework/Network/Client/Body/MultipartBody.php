<?php

namespace Ppm\Framework\Network\Client\Body;

use CURLFile;
use Ppm\Framework\Network\ContentTypes;

class MultipartBody extends RequestBodyBase
{
    private array $fields;
    private array $files;

    private string $boundary;

    public function __construct(array $fields = [], array $files = [])
    {
        $this->fields = $fields;
        $this->files = $files;
        $this->boundary = uniqid(str_repeat('-', 15));
    }

    public function set(string $key, string $value): static
    {
        $this->fields[$key] = $value;
        return $this;
    }

    public function addFile(string $key, string $name, string $path, string $contentType = 'text/plain'): static
    {
        $this->fields[$key] = [$path, $name, $contentType];
    }

    public function buildRequestBody(): string
    {
        $lines = [];
        foreach ($this->fields as $key => $value) {
            $lines[] = sprintf("--%s", $this->boundary);
            $lines[] = sprintf("Content-Disposition: form-data; name=\"%s\"\n", $key);
            $lines[] = sprintf("%s", $value);
        }

        foreach ($this->files as $key => $info) {
            [$path, $name, $contentType] = $info;
            $lines[] = sprintf("--%s", $this->boundary);
            $lines[] = sprintf("Content-Disposition: form-data; name=\"%s\"; filename=\"%s\"\n", $key, $name);
            $lines[] = sprintf("Content-Type: %s;", $contentType);
            $lines[] = sprintf("%s", file_get_contents($path));
        }
        $lines[] = sprintf("--%s--", $this->boundary);

        return implode("\n\r", $lines);
    }

    public function toCurl($curl): void
    {
        $data = $this->fields;
        foreach ($this->files as $key => $info) {
            [$path, $name, $contentType] = $info;
            $data[$key] = new CURLFile($path, $contentType, $name);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    public function getContentType(): string
    {
        return ContentTypes::MULTIPART . '; boundary=' . $this->boundary;
    }
}