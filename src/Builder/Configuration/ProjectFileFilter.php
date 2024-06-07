<?php

namespace Builder\Configuration;

use Builder\Configuration\Contracts\IConfigurationFileFilter;

class ProjectFileFilter implements IConfigurationFileFilter
{
    private array $filter;

    public function __construct(array $filter)
    {
        $this->filter = $filter;
    }

    public function getExclude(): ?string
    {
        return $this->filter['exclude'] ?? null;
    }

    public function hasExclude(): bool
    {
        return !empty($this->filter['exclude']);
    }

    public function getInclude(): string
    {
        return $this->filter['include'];
    }
}