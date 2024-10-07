<?php

namespace Ppm\Packages\Sources;

use JsonSerializable;

class Source implements JsonSerializable
{
    private string $path;
    private ?string $token;

    private string $id;

    /**
     * @param string $path
     * @param ?string $token
     */
    public function __construct(string $path, string $id = null, ?string $token = null)
    {
        $this->path = $path;
        $this->token = $token;
        $this->id = $id ?? md5(uniqid(rand(), true));
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

    public function getId(): string
    {
        return $this->id;
    }

    public function hasToken(): bool
    {
        return !is_null($this->token);
    }

    public function makeAuthHeaders(): array
    {
        return [
            'Authorization: bearer ' . $this->token,
        ];
    }

    public function jsonSerialize(): array
    {
        return [
            'path' => $this->path,
            'token' => $this->token,
            'id' => $this->id
        ];
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}