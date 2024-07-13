<?php

namespace Builder\Configuration\Contracts;

use Builder\Configuration\Actions;

interface IProjectConfiguration extends IConfigurationFileFilter
{
    public function getName(): string;

    public function getDirectory(): string;

    public function getEntrypoint(): ?string;

    public function hasVersion(): bool;

    public function hasEntrypoint(): bool;

    public function hasFiles(): bool;

    public function hasResources(): bool;

    public function hasIncludes(): bool;

    public function getVersion(): ?string;

    public function setVersion(string $version): void;

    public function getRunner(): ?string;

    public function getStub(): ?string;

    public function getStubContent(): string;

    public function hasStub(): bool;

    public function getAuthor(): ?string;

    public function getDescription(): ?string;

    public function getProjectReferences(): array;

    public function getPackageReferences(): array;

    /**
     * @return IConfigurationFileFilter[]
     */
    public function getIncludes(): array;

    /**
     * @return IConfigurationFileFilter[]
     */
    public function getFiles(): array;

    /**
     * @return IConfigurationFileFilter[]
     */
    public function getResources(): array;

    public function addDepend(string $depend): void;

    public function getDepends(): array;

    public function getActions(): Actions;
}