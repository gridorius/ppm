<?php

namespace Ppm\Framework\Crypto;

class HashKey
{
    private string $key;
    protected string $algorithm;

    public function __construct(string $key, string $algorithm = 'SHA256')
    {
        $this->key = $key;
        $this->algorithm = $algorithm;
    }

    public function sign(string $data): string
    {
        return hash_hmac($this->algorithm, $data, $this->key);
    }

    public function validate(string $data, string $signed): bool
    {
        return hash_equals($this->sign($data), $signed);
    }
}