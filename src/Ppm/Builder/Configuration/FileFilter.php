<?php

namespace Ppm\Builder\Configuration;

use Ppm\Framework\Exceptions\NullReferenceException;

class FileFilter
{
    protected string $include;
    protected int $offset;
    protected ?string $as;
    protected ?string $exclude;

    public function __construct(array $filter)
    {
        if (is_null($filter['include']))
            throw new NullReferenceException("Including mask is null");

        $this->include = $filter['include'];
        $this->offset = $filter['offset'] ?? 0;
        $this->as = $filter['as'] ?? null;
        $this->exclude = $filter['exclude'] ?? null;
    }

    public function getAs(): ?string
    {
        return $this->as;
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

    public function getOffset(): int
    {
        return $this->offset;
    }
}