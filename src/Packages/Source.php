<?php

namespace Packages;

use Packages\Contracts\ISource;

class Source implements ISource
{
    private string $path;
    private ?string $token;

    /**
     * @param string $path
     * @param ?string $token
     */
    public function __construct(string $path, ?string $token = null)
    {
        $this->path = $path;
        $this->token = $token;
    }

    public function makeRequestPath(string $path): string
    {
        return $this->path . '/' . $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function hasToken(): bool
    {
        return !is_null($this->token);
    }

    public function makeAuthHeaders(): array
    {
        return [
            'Authorization: bearer ' . $this->token
        ];
    }

    public function jsonSerialize(): array
    {
        return [
            'path' => $this->path,
            'token' => $this->getToken()
        ];
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}