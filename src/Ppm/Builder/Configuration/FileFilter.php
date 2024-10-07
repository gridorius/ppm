<?php

namespace Ppm\Builder\Configuration;

use Builder\Configuration\Contracts\IConfigurationFileFilter;
use Ppm\Exceptions\NullReferenceException;

class FileFilter
{
    protected string $include;
    protected ?string $exclude;

    public function __construct(array $filter)
    {
        if (is_null($filter['include']))
            throw new NullReferenceException("Including mask is null");

        $this->include = $filter['include'];
        $this->exclude = $filter['exclude'] ?? null;
    }

    public function hasExclude(): bool
    {
        return !empty($this->exclude);
    }

    public function getInclude(): string
    {
        return $this->include;
    }

    public function getExclude(): ?string
    {
        return $this->exclude;
    }
}