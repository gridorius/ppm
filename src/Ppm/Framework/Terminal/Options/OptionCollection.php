<?php

namespace Ppm\Framework\Terminal\Options;

class OptionCollection
{
    private array $options = [];

    public function setValue(string $option, string $value): void
    {
        $this->options[$option][] = $value;
    }

    public function incrementOption(string $option): void
    {
        if (!key_exists($option, $this->options))
            $this->options[$option] = 0;

        $this->options[$option]++;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}