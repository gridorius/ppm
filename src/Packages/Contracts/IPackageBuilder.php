<?php

namespace Packages\Contracts;

interface IPackageBuilder
{
    public function build(string $pathToProjectFile): void;

    public function buildResourcesPackage(string $buildDirectory): void;
}