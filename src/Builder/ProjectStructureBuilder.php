<?php

namespace Builder;

use Builder\Contracts\IProjectInfo;
use Builder\Contracts\IProjectStructure;
use Builder\Contracts\IProjectStructureBuilder;

class ProjectStructureBuilder implements IProjectStructureBuilder
{
    public function build(IProjectInfo $projectInfo): IProjectStructure
    {
        $projectStructure = new ProjectStructure($projectInfo);
        $this->findTypeFiles($projectStructure);
        $this->findMoveFiles($projectStructure);
        $this->findResources($projectStructure);
        $this->findIncludes($projectStructure);
        return $projectStructure;
    }

    public function findTypeFiles(IProjectStructure $structure): void
    {
        $configuration = $structure->getProjectInfo()->getConfiguration();
        $manifestBuilder = $structure->getManifestBuilder();
        $files = $structure->getProjectInfo()->filterFiles($configuration);
        $types = [];
        foreach ($files as $path => $relativePath) {
            $foundTypes = EntityFinder::findByTokens($path);
            foreach ($foundTypes as $type) {
                $localPath = preg_replace("/\\\\/", '.', $type);
                $types[$type] = $localPath;
                $structure->addPharFile($localPath, $path);
            }
        }
        $manifestBuilder->setTypes($types);
    }

    public function findMoveFiles(IProjectStructure $structure): void
    {
        $configuration = $structure->getProjectInfo()->getConfiguration();
        if (!$configuration->hasFiles()) return;

        $files = $structure->getProjectInfo()->filterFilesByFiltersArray($configuration->getFiles());
        foreach ($files as $realPath => $relativePath) {
            $structure->addOutFile($relativePath, $realPath);
        }
    }

    public function findResources(IProjectStructure $structure): void
    {
        $configuration = $structure->getProjectInfo()->getConfiguration();
        if (!$configuration->hasResources()) return;

        $resourceFiles = $structure->getProjectInfo()->filterFilesByFiltersArray($configuration->getResources());
        $resources = [];
        foreach ($resourceFiles as $path => $relativePath) {
            $localPath = $relativePath;
            $resources[$relativePath] = $localPath;
            $structure->addPharFile($localPath, $path);
        }
        $structure->getManifestBuilder()->setResources($resources);
    }

    public function findIncludes(IProjectStructure $structure): void
    {
        $configuration = $structure->getProjectInfo()->getConfiguration();
        if (!$configuration->hasIncludes()) return;
        $includeFiles = $structure->getProjectInfo()->filterFilesByFiltersArray($configuration->getIncludes());
        $includes = [];
        foreach ($includeFiles as $path => $relativePath) {
            $localPath = $this->makeInnerPath($path);
            $includes[] = $localPath;
            $structure->addPharFile($localPath, $path);
        }
        $structure->getManifestBuilder()->setIncludes($includes);
    }

    private function makeInnerPath(string $path): string
    {
        return hash_file('sha256', $path) . '.' . pathinfo($path, PATHINFO_EXTENSION);
    }
}