<?php

namespace Packages\Contracts;

use Countable;
use Iterator;

/**
 * Iterato
 */
interface ISources extends Iterator, Countable
{
    public function add(ISource $source, ?string $alias = null): void;

    public function delete(string $source): void;

    public function get(string $source): ?ISource;

    public function has(string $source): bool;

    public function current(): ISource;

    public function key(): string;

    public function authorize(string $source, string $login, string $password, ?string $alias = null): void;

    public function createSource(string $path, ?string $alias = null): ISource;
}