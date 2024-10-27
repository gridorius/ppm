<?php

namespace Ppm\Builder\Actions;

use Ppm\Framework\Utils\StringUtils;

abstract class ActionBase implements IAction
{
    const BUILD_DIRECTORY = "/\\$\(buildDirectory\)/";
    const OUT_DIRECTORY = "/\\$\(outDirectory\)/";
    protected string $outDirectory;
    protected string $buildDirectory;

    public function setDirectories(string $buildDirectory, string $outDirectory): void
    {
        $this->buildDirectory = $buildDirectory;
        $this->outDirectory = $outDirectory;
    }

    public function prepareString(string $data): string
    {
        return StringUtils::replace(
            $data,
            [
                static::BUILD_DIRECTORY => $this->buildDirectory,
                static::OUT_DIRECTORY => $this->outDirectory,
            ]);
    }
}