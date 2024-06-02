<?php

namespace Packages\Http;

class Response
{
    protected array $headers;
    protected string $body;

    public function __construct(array $headers, string $body)
    {
        $this->headers = $headers;
        $this->body = $body;
    }

    public function json(): array
    {
        $data = json_decode($this->body, true);
        if (is_null($data))
            throw new \Exception("Failed parse json data: " . json_last_error_msg());

        return $data;
    }

    public function text(): string
    {
        return $this->body;
    }

    public function saveAsFile(string $path)
    {
        file_put_contents($path, $this->body);
    }

    public function awaitCode(int $code, \Closure $handler): Response
    {
        if ($this->headers['code'] == $code)
            $handler($this);
        return $this;
    }

    public function awaitCodes(array $codes, \Closure $handler): Response
    {
        if (in_array($this->headers['code'], $codes))
            $handler($this);

        return $this;
    }
}