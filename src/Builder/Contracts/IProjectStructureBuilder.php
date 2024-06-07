<?php

namespace Builder\Contracts;

interface IProjectStructureBuilder
{
    public function build(IProjectInfo $projectInfo): IProjectStructure;
}