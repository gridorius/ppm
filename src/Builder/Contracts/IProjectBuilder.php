<?php

namespace Builder\Contracts;

interface IProjectBuilder
{
    public function build(IProjectStructure $projectStructure, string $outDirectory): void;
}