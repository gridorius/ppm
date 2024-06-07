<?php

namespace Builder\Configuration\Contracts;

interface IConfigurationFileFilter
{
    public function getInclude(): string;

    public function getExclude(): ?string;

    public function hasExclude(): bool;
}