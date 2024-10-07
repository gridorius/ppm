<?php

namespace Ppm\Builder\Configuration;

use Ppm\Builder\BuildContext;
use Ppm\Builder\ContextBuilder;
use Ppm\Builder\FileStructure;
use Ppm\Builder\ProjectFiles;
use Ppm\Packages\Common\PackageUtils;

class ConfigurationCollection
{
    /**
     * @var Configuration[]
     */
    private array $configurations = [];

    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    public function mergeCollection(ConfigurationCollection $collection): void
    {
        $this->configurations = array_merge($this->configurations, $collection->getConfigurationsArray());
    }

    public function getConfigurationsArray(): array
    {
        return $this->configurations;
    }

    /**
     * @return BuildContext[]
     */
    public function buildProjectsContexts(): array
    {
        $contexts = [];
        $fileStructure = new FileStructure();
        foreach ($this->configurations as $configuration) {
            $projectDirectory = $configuration->getDirectory();
            if (!$fileStructure->hasProject($projectDirectory))
                $fileStructure->scanDirectory($projectDirectory);

            $projectFiles = new ProjectFiles($fileStructure->getProjectFiles($projectDirectory), $configuration);
            $contexts[] = ContextBuilder::build($projectFiles, $configuration);
        }

        return $contexts;
    }

    public function getPackages(): array
    {
        $packages = [];
        foreach ($this->configurations as $configuration)
            foreach ($configuration->getPackages() as $name => $version)
                $packages[] = PackageUtils::makePackageName($name, $version);

        return $packages;
    }
}