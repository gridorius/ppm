<?php

namespace Packages\Contracts;

use JsonSerializable;

interface ISource extends JsonSerializable
{
    public function makeRequestPath(string $path): string;

    public function getPath(): string;

    public function getToken(): string;

    public function hasToken(): bool;

    public function makeAuthHeaders(): array;
}