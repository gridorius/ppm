<?php

namespace Ppm\Builder;

use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\Manifest;

class BuildContext
{
    private ProjectFiles $projectFiles;
    private Configuration $configuration;
    private Manifest $manifest;
    private array $innerFiles;
    private array $outerFiles;

    /**
     * @param Configuration $configuration
     * @param Manifest $manifest
     * @param array $innerFiles
     * @param array $outerFiles
     */
    public function __construct(ProjectFiles $projectFiles, Configuration $configuration, Manifest $manifest, array $innerFiles, array $outerFiles)
    {
        $this->projectFiles = $projectFiles;
        $this->configuration = $configuration;
        $this->manifest = $manifest;
        $this->innerFiles = $innerFiles;
        $this->outerFiles = $outerFiles;
    }

    public function getProjectFiles(): ProjectFiles
    {
        return $this->projectFiles;
    }

    public function getHash(): string
    {
        $hashString = '';
        foreach ($this->innerFiles as $path)
            $hashString .= hash_file('sha256', $path);
        foreach ($this->outerFiles as $path)
            $hashString .= hash_file('sha256', $path);

        return hash('sha256', $hashString);
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getManifest(): Manifest
    {
        return $this->manifest;
    }

    public function getInnerFiles(): array
    {
        return $this->innerFiles;
    }

    public function getOuterFiles(): array
    {
        return $this->outerFiles;
    }
}