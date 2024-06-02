<?php

namespace Builder;

use Utils\PathUtils;

class RecursiveConfigurationScanner
{
    protected string $buildDir;
    protected array $configuration;
    protected array $structure = [];
    protected array $projects = [];
    protected array $depends = [];
    protected array $packages = [];

    public function __construct(string $pathToProj)
    {
        $this->buildDir = dirname(PathUtils::preparePathForWindows($pathToProj));
        $this->configuration = PathUtils::getJson($pathToProj);
        $this->projects[$this->buildDir] = $this->configuration;
    }

    public function scan(): void{
        $this->structure = Scanner::scanDirectory($this->buildDir);
        if (!empty($this->configuration['projectReferences']))
            $this->scanProjectReferences($this->buildDir, $this->configuration['projectReferences']);
    }

    public function getConfiguration(): array{
        return $this->configuration;
    }

    public function getProjectConfiguration(string $projectPath): array{
        return $this->projects[$projectPath];
    }

    public function getPackages(): array{
        return $this->packages;
    }

    public function getProjects(): array{
        return $this->projects;
    }

    public function getProjectStructure(string $projectPath): array{
        return $this->structure[$projectPath];
    }

    public function getProjectDepends(string $projectPath): array{
        return $this->depends[$projectPath] ?? [];
    }

    protected function scanProjectReferences(string $projectDir, array $references)
    {
        $depends = [];
        foreach ($references as $reference) {
            $configPath = PathUtils::resolveRelativePath($projectDir, $reference);
            $referenceDir = dirname($configPath);
            if (!key_exists($referenceDir, $this->projects)) {
                $config = $this->projects[$referenceDir] = PathUtils::getJson($configPath);
                $depends[] = $config['name'];
                if (!key_exists($referenceDir, $this->structure)) {
                    $structure = Scanner::scanDirectory($referenceDir);
                    foreach ($structure as $projectPath => $projectFiles)
                        $this->structure[$projectPath] = $projectFiles;
                }
                if (!empty($config['packageReferences']))
                    foreach ($config['packageReferences'] as $name => $version) {
                        $depends[] = $name . '.phar';
                        $this->packages[$name] = $version;
                    }
                if (!empty($config['projectReferences']))
                    $this->scanProjectReferences($referenceDir, $config['projectReferences']);
            } else {
                $depends[] = $this->projects[$referenceDir]['name'];
            }
        }

        $this->depends[$projectDir] = $depends;
    }
}