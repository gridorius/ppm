<?php

namespace Builder\Configuration;

use Builder\Configuration\Contracts\IConfigurationFileFilter;
use Utils\PathUtils;

class ProjectFileFilter implements IConfigurationFileFilter
{
    private array $filter;

    public function __construct(array $filter)
    {
        $this->filter = $filter;
        if (WIN) {
            $this->filter['include'] = PathUtils::preparePathForWindows($this->filter['include']);
            if ($this->hasExclude())
                $this->filter['exclude'] = PathUtils::preparePathForWindows($this->filter['exclude']);
        }
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