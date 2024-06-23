<?php

namespace Builder\Configuration;

use Builder\Configuration\Contracts\IConfigurationFileFilter;
use Common\Exceptions\NullReferenceException;

class ProjectFileFilter implements IConfigurationFileFilter
{
    private array $filter;

    public function __construct(array $filter)
    {
        if (is_null($filter['include']))
            throw new NullReferenceException("Including mask is null");
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