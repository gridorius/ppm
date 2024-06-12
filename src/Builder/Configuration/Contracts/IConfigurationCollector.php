<?php

namespace Builder\Configuration\Contracts;

interface IConfigurationCollector
{
    public function collectFromProjectFile(string $pathToProjectFile): IConfigurationCollection;

    /**
     * @param string $pathToProjectFile
     * @param IConfigurationCollection $configurationCollection
     * @return IProjectConfiguration
     *
     * recursively adds configurations to the collection including project references
     */
    public function collectReference(string $pathToProjectFile, IConfigurationCollection $configurationCollection): IProjectConfiguration;
}