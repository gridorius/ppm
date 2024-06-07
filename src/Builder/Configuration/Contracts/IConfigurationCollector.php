<?php

namespace Builder\Configuration\Contracts;

interface IConfigurationCollector
{
    public function collect(string $pathToProjectFile): IConfigurationCollection;
}