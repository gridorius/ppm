<?php

namespace Ppm\Builder;

class BuildContextCollection
{
    /**
     * @var BuildContext[] $contexts ;
     */
    private array $contexts;

    public function __construct(array $contexts)
    {
        $this->contexts = $contexts;
    }

    public function toArray(): array
    {
        return $this->contexts;
    }

    public function getHash(): string
    {
        $hashesString = '';
        foreach ($this->contexts as $context)
            $hashesString .= $context->getHash();
        return hash("sha256", $hashesString);
    }
}