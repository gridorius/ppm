<?php

namespace Terminal;

class MultilineOutput
{
    protected int $lastLines = 0;

    public function update(string $data): void
    {
        echo "\033[{$this->lastLines}F\033[{$this->lastLines}M";
        $this->lastLines = preg_match_all("/\n/", $data);
        echo $data;
    }

    public function subLine(): void
    {
        $this->lastLines -= 1;
    }
}