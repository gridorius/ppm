<?php

namespace Ppm\Framework\Network\Client;

use Closure;
use Exception;
use SimpleXMLElement;

class Response
{
    private array $headers;
    private array $headerOptions;
    private int $code;
    private string $status;
    private string $content;

    public function __construct(int $code = -1, string $status = '', array $headers = [], array $headerOptions = [], string $content = '')
    {
        $this->code = $code;
        $this->status = $status;
        $this->headers = $headers;
        $this->headerOptions = $headerOptions;
        $this->content = $content;
    }

    public function setHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function getLength(): int
    {
        return strlen($this->content);
    }

    public function setHeaderOption(string $name, array $value): static
    {
        $this->headerOptions[$name] = array_merge($this->headerOptions[$name] ?? [], $value);
        return $this;
    }

    public function setStatusCode(int $code, string $status = ''): static
    {
        $this->code = $code;
        $this->status = $status;
    }

    public function addContent(string $content): static
    {
        $this->content .= $content;
        return $this;
    }

    public function getHeaderOptions(): array
    {
        return $this->headerOptions;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function json(): mixed
    {
        $data = json_decode($this->content, true);
        if (is_null($data))
            throw new Exception(json_last_error_msg());

        return $data;
    }

    public function unSerialize(): mixed
    {
        return unserialize($this->content);
    }

    public function jsonObject(): mixed
    {
        $data = json_decode($this->content);
        if (is_null($data))
            throw new Exception(json_last_error_msg());

        return $data;
    }

    public function text(): string
    {
        return $this->content;
    }

    public function int(): int
    {
        return intval($this->content);
    }

    public function xml(): SimpleXMLElement
    {
        return simplexml_load_string(
            $this->content,
            SimpleXMLElement::class,
            LIBXML_COMPACT | LIBXML_PARSEHUGE
        );
    }

    public function awaitCode(int $code, Closure $handler): Response
    {
        if ($this->getCode() == $code)
            $handler($this);
        return $this;
    }

    public function awaitCodes(array $codes, Closure $handler): Response
    {
        if (in_array($this->getCode(), $codes))
            $handler($this);

        return $this;
    }
}