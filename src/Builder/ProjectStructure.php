<?php

namespace Builder;

use ArrayIterator;
use Builder\Configuration\Contracts\IManifestBuilder;
use Builder\Configuration\Contracts\IManifestInformation;
use Builder\Configuration\ManifestBuilder;
use Builder\Contracts\IProjectInfo;
use Builder\Contracts\IProjectStructure;

class ProjectStructure implements IProjectStructure
{
    private array $pharFiles;
    private array $outerFiles;
    private IProjectInfo $projectInfo;
    private IManifestBuilder $manifestBuilder;

    public function __construct(IProjectInfo $projectInfo)
    {
        $this->pharFiles = [];
        $this->outerFiles = [];
        $this->projectInfo = $projectInfo;
        $manifestBuilder = $this->manifestBuilder = new ManifestBuilder();
        $configuration = $this->projectInfo->getConfiguration();
        $manifestBuilder->setName($configuration->getName());
        $manifestBuilder->setVersion($configuration->getVersion());
        $manifestBuilder->setDepends($configuration->getDepends());
    }

    public function getProjectInfo(): IProjectInfo
    {
        return $this->projectInfo;
    }

    public function getManifestBuilder(): IManifestBuilder
    {
        return $this->manifestBuilder;
    }

    public function getManifestInfo(): IManifestInformation
    {
        return $this->manifestBuilder;
    }

    public function addPharFile(string $innerPath, string $realPath): void
    {
        $this->pharFiles[$innerPath] = $realPath;
    }

    public function addOutFile(string $relativePath, string $realPath): void
    {
        $this->outerFiles[$realPath] = $realPath;
    }

    public function getPharFilesIterator(): ArrayIterator
    {
        return new ArrayIterator($this->pharFiles);
    }

    public function getOuterFiles(): array
    {
        return $this->outerFiles;
    }
}