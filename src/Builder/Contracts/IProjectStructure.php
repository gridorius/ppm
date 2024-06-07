<?php

namespace Builder\Contracts;

use ArrayIterator;
use Builder\Configuration\Contracts\IManifestBuilder;
use Builder\Configuration\Contracts\IManifestInformation;

interface IProjectStructure
{
    public function getProjectInfo(): IProjectInfo;

    public function getManifestBuilder(): IManifestBuilder;

    public function getManifestInfo(): IManifestInformation;

    public function addPharFile(string $innerPath, string $realPath): void;

    public function addOutFile(string $relativePath, string $realPath): void;

    public function getPharFilesIterator(): ArrayIterator;

    public function getOuterFiles(): array;
}