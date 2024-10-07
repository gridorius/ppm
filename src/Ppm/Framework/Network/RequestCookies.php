<?php

namespace Ppm\Framework\Network;

class RequestCookies
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function set(string $name, string $value): static
    {
        $this->data[$name] = $value;
        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function buildHeader(): string
    {
        $cookies = [];
        foreach ($this->data as $name => $value)
            $cookies[] = $name . "=" . $value;

        return 'Cookie: ' . implode(";", $cookies);
    }
}