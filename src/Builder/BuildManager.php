<?php

namespace Builder;

use Exception;
use Utils\Timer;

class BuildManager
{
    protected RecursiveConfigurationScanner $scanner;

    public function __construct(string $pathToProj)
    {
        $this->scanner = new RecursiveConfigurationScanner($pathToProj);
    }

    public function getScanner(): RecursiveConfigurationScanner{
        return $this->scanner;
    }

    public function build(string $outDir): void
    {
        try {
            $timer = new Timer();
            $this->scanner->scan();
            $scanPassed = $timer->getPassed();
            echo "Scanning passed in {$scanPassed}s\n";
            $this->buildProjects($outDir);
            $passed = $timer->getPassed();
            echo "Build is completed in {$passed}s\n";
            echo "Output directory: {$outDir}\n";
        } catch (Exception $exception) {
            echo "Build failed\n";
            echo $exception->getMessage() . "\n";
        }
    }

    protected function buildProjectStructures(): array
    {
        $structures = [];
        foreach ($this->scanner->getProjects() as $path => $config) {
            $structure = new ProjectStructureBuilder($path, $config, $this->scanner->getProjectStructure($path));
            $structures[$path] = $structure->build();
        }
        return $structures;
    }

    protected function buildProjects(string $outDir): void
    {
        foreach ($this->scanner->getProjects() as $path => $config) {
            $timer = new Timer();
            $structure = $this->buildProjectStructure($path, $config);
            $depends = $this->scanner->getProjectDepends($path);
            $project = new ProjectBuilder($path, $structure);
            $project->build($outDir);
            $passed = $timer->getPassed();
            echo "\033[1m\033[32m{$structure->manifest['name']}\033[0m:\033[34m{$structure->manifest['version']}\033[0m built in {$passed}s\n";
            $typeCount = count($structure->manifest['types']);
            $resourceCount = count($structure->manifest['resources']);
            $includeCount = count($structure->manifest['includes']);
            $dependsCount = count($depends);
            echo "\tTypes: {$typeCount}\tResources: {$resourceCount}\tIncludes: {$includeCount}\tDepends: {$dependsCount}\n";
        }
    }

    protected function buildProjectStructure(string $path, array $config): ProjectStructure{
        $structureBuilder = new ProjectStructureBuilder(
            $path, $config, $this->scanner->getProjectStructure($path), $this->scanner->getProjectDepends($path)
        );
        return $structureBuilder->build();
    }
}