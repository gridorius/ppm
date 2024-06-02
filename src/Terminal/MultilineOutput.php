<?php

namespace Terminal;

class MultilineOutput
{
    protected int $lastLines = 0;

    public function update(string $data)
    {
        $this->updateArray(explode("\n", $data));
    }

    public function updateArray(array $rows)
    {
        for ($i = 0; $i < $this->lastLines; $i++)
            echo "\033[2K\033[1F";

        $this->lastLines = count($rows) - 1;
        echo implode("\033[1E", $rows);
    }
}