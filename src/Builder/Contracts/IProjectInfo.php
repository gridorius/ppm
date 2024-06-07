<?php

namespace Builder\Contracts;

use Builder\Configuration\Contracts\IConfigurationFileFilter;
use Builder\Configuration\Contracts\IProjectConfiguration;

interface IProjectInfo
{
    public function getConfiguration(): IProjectConfiguration;

    public function getFiles(): array;

    public function filterFiles(IConfigurationFileFilter $filter): array;

    /**
     * @param IConfigurationFileFilter[] $filters
     * @return array
     */
    public function filterByArray(array $filters): array;
}