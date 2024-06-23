<?php

namespace Builder\Contracts;

use Builder\Configuration\Contracts\IConfigurationCollection;

interface IBuildManager
{
    public function buildFromConfigurationCollection(IConfigurationCollection $configurationCollection, string $outDirectory): void;

    public function build(string $pathToProjectFile, string $outDirectory): void;

    public function AddAssemblyPhar(string $outDirectory): void;
}