<?php

namespace Ppm\Framework\Network\Client;

use Ppm\Framework\Network\Client\Body\RequestBodyBase;
use Ppm\Framework\Network\Constants\RequestMethods;
use Ppm\Framework\Network\RequestCookies;

class HttpRequest
{
    private string $url;
    private string $method;
    private array $headers = [];
    private array $params = [];
    private RequestCookies $_cookies;
    private RequestBodyBase $body;

    public function __construct(string $url, string $method = RequestMethods::GET)
    {
        $this->url = $url;
        $this->method = $method;
        $this->_cookies = new RequestCookies();
    }

    public function getAction(): HttpRequestAction
    {
        return new HttpRequestAction($this);
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getUrlData(): array
    {
        return parse_url($this->url);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setHeaders($headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    public function addHeader(string $header): static
    {
        $this->headers[] = $header;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setParams($query): static
    {
        $this->params = $query;
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setBody(RequestBodyBase $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function setCookies(): RequestCookies
    {
        return $this->_cookies;
    }

    public function getBody(): ?RequestBodyBase
    {
        return $this->body ?? null;
    }
}