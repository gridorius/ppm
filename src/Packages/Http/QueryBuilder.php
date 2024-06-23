<?php

namespace Packages\Http;

class QueryBuilder
{
    protected Client $client;
    protected string $method = 'GET';
    protected string $url;
    protected array $headers = [];

    protected array $query = [];

    protected string|array|null $body = null;

    public function __construct(string $url, string $method, Client $client)
    {
        $this->client = $client;
        $this->method = $method;
        $this->url = $url;
    }

    public function headers(array $headers): QueryBuilder
    {
        $this->headers = $headers;
        return $this;
    }

    public function query(array $query): QueryBuilder
    {
        $this->query = $query;
        return $this;
    }

    public function body($body): QueryBuilder
    {
        $this->body = $body;
        return $this;
    }

    public function execute(): Response
    {
        return $this->client->executeQuery($this->url, $this->method, $this->headers, $this->query, $this->body);
    }
}