<?php

namespace Packages\Contracts;

interface IPackageBuilder
{
    public function build(string $pathToProjectFile): void;
}