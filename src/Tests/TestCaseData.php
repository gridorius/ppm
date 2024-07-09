<?php

namespace Tests;

class TestCaseData
{
    private ?string $name;
    private array $data;

    public function __construct(...$data)
    {
        $this->name = null;
        $this->data = $data;
    }

    public static function create(...$data): TestCaseData
    {
        return new static(...$data);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function hasName(): bool
    {
        return !is_null($this->name);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): TestCaseData
    {
        $this->name = $name;
        return $this;
    }
}