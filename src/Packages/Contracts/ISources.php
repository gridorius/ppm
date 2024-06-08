<?php

namespace Packages\Contracts;

use Countable;
use Iterator;

/**
 * Iterato
 */
interface ISources extends Iterator, Countable
{
    public function add(ISource $source): void;

    public function delete(ISource $source): void;

    public function get(string $source): ?ISource;

    public function has(string $source): bool;

    public function current(): ISource;

    public function key(): string;

    public function authorize(string $source, string $login, string $password): void;
}