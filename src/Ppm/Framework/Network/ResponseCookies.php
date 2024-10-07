<?php

namespace Ppm\Framework\Network;

class ResponseCookies
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function set(string $name, string $value, int $lifeTime, string $path = "/", string $domain = "", bool $secure = false, bool $httpOnly = false): static
    {
        $this->data[$name] = [
            'value' => $value,
            'options' => [
                'Expires' => date('r', time() + $lifeTime),
                'Path' => $path,
                'Domain' => $domain,
                'Secure' => $secure,
                'HttpOnly' => $httpOnly
            ]
        ];
        return $this;
    }

    public function buildHeaders(): array
    {
        $headers = [];
        foreach ($this->data as $name => $params) {
            $value = $params['value'];
            $options = [];
            foreach ($params['options'] as $option => $value) {
                if (!empty($option)) {
                    if (is_bool($value))
                        $options[] = $option;
                    if (is_string($value))
                        $options[] = "{$option}={$value}";
                }
            }
            $headers[] = "Set-Cookie: {$name}={$value}; " . implode("; ", $options);
        }

        return $headers;
    }
}