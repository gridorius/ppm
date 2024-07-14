<?php

namespace Builder\Configuration;

use Builder\Configuration\Contracts\IConfigurationCollection;
use Builder\Configuration\Contracts\IProjectConfiguration;
use Builder\Contracts\IProjectInfo;
use Builder\FileStructure;
use Builder\ProjectInfo;

class ConfigurationCollection implements IConfigurationCollection
{
    /**
     * @var IProjectConfiguration[]
     */
    private array $configurations = [];
    private IProjectConfiguration $main;

    public function addConfiguration(string $projectDirectory, IProjectConfiguration $configuration): void
    {
        $this->configurations[$projectDirectory] = $configuration;
    }

    public function setMainConfiguration(IProjectConfiguration $configuration): void
    {
        $this->main = $configuration;
    }

    public function getMainConfiguration(): IProjectConfiguration
    {
        return $this->main;
    }

    public function getConfigurationsArray(): array
    {
        return $this->configurations;
    }

    public function hasConfiguration(string $projectDirectory): bool
    {
        return key_exists($projectDirectory, $this->configurations);
    }

    public function getConfiguration(string $projectDirectory): IProjectConfiguration
    {
        return $this->configurations[$projectDirectory];
    }

    public function setVersionIfEmpty(string $version): void
    {
        foreach ($this->configurations as $projectConfiguration)
            if (!$projectConfiguration->hasVersion())
                $projectConfiguration->setVersion($version);
    }

    /**
     * @return IProjectInfo[]
     */
    public function buildProjectInfos(): array
    {
        $projectInfoList = [];
        $projectFiles = new FileStructure();
        foreach ($this->configurations as $projectDirectory => $projectConfiguration) {
            if (!$projectFiles->hasProject($projectDirectory))
                $projectFiles->scanDirectory($projectDirectory);

            $projectInfo = new ProjectInfo($projectFiles->getProjectFiles($projectDirectory), $projectConfiguration);
            $projectInfoList[] = $projectInfo;
        }

        return $projectInfoList;
    }

    public function getDepends(): array
    {
        $packages = [];
        foreach ($this->configurations as $configurationWrapper)
            foreach ($configurationWrapper->getPackageReferences() as $name => $version)
                $packages[$name] = $version;

        return $packages;
    }
}