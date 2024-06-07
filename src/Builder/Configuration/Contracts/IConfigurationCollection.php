<?php

namespace Builder\Configuration\Contracts;

use Builder\Contracts\IProjectInfo;

interface IConfigurationCollection
{
    public function setMainConfiguration(IProjectConfiguration $configuration);

    public function getMainConfiguration(): IProjectConfiguration;

    public function add(string $projectDirectory, IProjectConfiguration $configuration): void;

    public function hasConfiguration(string $projectDirectory): bool;

    public function getConfiguration(string $projectDirectory): IProjectConfiguration;

    public function setVersionIfEmpty(string $version): void;

    /**
     * @return IProjectConfiguration[]
     */
    public function getAll(): array;

    public function getPackages(): array;

    /**
     * @return IProjectInfo[]
     */
    public function buildProjectInfos(): array;
}