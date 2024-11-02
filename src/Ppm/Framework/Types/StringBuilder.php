<?php

namespace Ppm\Framework\Types;

use Stringable;

class StringBuilder implements Stringable
{
    protected string $string;
    const EMPTY = '';

    public function __construct(string $string = self::EMPTY)
    {
        $this->string = $string;
    }

    public static function from(string $string): static
    {
        return new static($string);
    }

    public function __toString(): string
    {
        return $this->string;
    }

    public function toLower(): self
    {
        $this->string = strtolower($this->string);
        return $this;
    }

    public function toUpper(): self
    {
        $this->string = strtoupper($this->string);
        return $this;
    }

    public function trim(): self
    {
        $this->string = trim($this->string);
        return $this;
    }

    public function explode($separator): array
    {
        return explode($separator, $this->string);
    }

    public function add($string): StringBuilder
    {
        $this->string .= $string;
        return $this;
    }

    public function getPos($substring): int
    {
        return strpos($substring, $this->string);
    }

    public function subString($offset, $length = null): self
    {
        return new static(substr($this->string, $offset, $length));
    }

    public function hash(string $algo): self
    {
        return new static(hash($algo, $this->string));
    }

    public function ucFirst(): self
    {
        $this->string = ucfirst($this->string);
        return $this;
    }

    public function ucWords($separator = " \t\r\n\f\v"): self
    {
        $this->string = ucwords($this->string, $separator);
        return $this;
    }

    public function match($pattern): ?array
    {
        $matches = [];
        $result = preg_match($pattern, $this->string, $matches);
        return $result ? $matches : null;
    }

    public function split($pattern): array
    {
        return preg_split($pattern, $this->string);
    }

    public function replace($pattern, $replacement = ''): ?self
    {
        $result = preg_replace($pattern, $replacement, $this->string);
        if ($result) {
            return new static($result);
        } else {
            return null;
        }
    }

    public function replaceCallback($pattern, callable $callback): self
    {
        $this->string = preg_replace_callback($pattern, $callback, $this->string);
        return $this;
    }

    public function format(...$values): string
    {
        return sprintf($this->string, ...$values);
    }
}