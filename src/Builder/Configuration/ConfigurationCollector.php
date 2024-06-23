<?php

namespace Builder\Configuration;

use Builder\Configuration\Contracts\IConfigurationCollection;
use Builder\Configuration\Contracts\IConfigurationCollector;
use Builder\Configuration\Contracts\IProjectConfiguration;
use Utils\PathUtils;

class ConfigurationCollector implements IConfigurationCollector
{
    /**
     * @param string $pathToProjectFile
     *
     *  Собирает список зависимых конфигураций
     */
    public function collectFromProjectFile(string $pathToProjectFile): IConfigurationCollection
    {
        $configurationCollection = new ConfigurationCollection();
        $mainConfiguration = $this->collectReference($pathToProjectFile, $configurationCollection);
        $configurationCollection->setMainConfiguration($mainConfiguration);
        return $configurationCollection;
    }

    public function collectReference(string $pathToProjectFile, IConfigurationCollection $configurationCollection): IProjectConfiguration
    {
        $projectDir = dirname(PathUtils::preparePathForWindows($pathToProjectFile));
        if ($configurationCollection->hasConfiguration($projectDir))
            return $configurationCollection->getConfiguration($projectDir);

        $projectConfiguration = new ProjectConfiguration($pathToProjectFile);
        $configurationCollection->addConfiguration($projectDir, $projectConfiguration);
        $this->collectReferences($projectConfiguration, $projectDir, $configurationCollection);
        return $projectConfiguration;
    }

    private function collectReferences(
        IProjectConfiguration    $projectConfiguration,
        string                   $projectDirectory,
        IConfigurationCollection $configurationCollection
    ): void
    {
        foreach ($projectConfiguration->getProjectReferences() as $referencePath) {
            $pathToProjectFile = PathUtils::resolveRelativePath($projectDirectory, $referencePath);
            $dependProject = $this->collectReference($pathToProjectFile, $configurationCollection);
            $projectConfiguration->addDepend($dependProject->getName());
        }
    }
}