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
                $localPath = 'types/' . $type . '.php';
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

        $files = $structure->getProjectInfo()->filterByArray($configuration->getFiles());
        foreach ($files as $realPath => $relativePath)
            $structure->addOutFile($relativePath, $realPath);
    }

    public function findResources(IProjectStructure $structure): void
    {
        $configuration = $structure->getProjectInfo()->getConfiguration();
        if (!$configuration->hasResources()) return;

        $resourceFiles = $structure->getProjectInfo()->filterByArray($configuration->getResources());
        $resources = [];
        foreach ($resourceFiles as $path => $relativePath) {
            $localPath = 'resources/' . $relativePath;
            $resources[$relativePath] = $localPath;
            $structure->addPharFile($localPath, $path);
        }
        $structure->getManifestBuilder()->setResources($resources);
    }

    public function findIncludes(IProjectStructure $structure): void
    {
        $configuration = $structure->getProjectInfo()->getConfiguration();
        if (!$configuration->hasIncludes()) return;
        $includeFiles = $structure->getProjectInfo()->filterByArray($configuration->getIncludes());
        $includes = [];
        foreach ($includeFiles as $path => $relativePath) {
            $localPath = 'includes/' . $relativePath;
            $includes[$relativePath] = $localPath;
            $structure->addPharFile($localPath, $path);
        }
        $structure->getManifestBuilder()->setIncludes($includes);
    }
}