<?php

namespace Ppm\Builder\Configuration;

use Ppm\Builder\BuildContext;
use Ppm\Builder\BuildContextCollection;
use Ppm\Builder\ContextBuilder;
use Ppm\Builder\FileStructure;
use Ppm\Builder\ProjectFiles;
use Ppm\Packages\Common\PackageUtils;

class ConfigurationCollection
{
    /**
     * @var Configuration[]
     */
    private array $configurations;
    private ?BuildContextCollection $contexts;

    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
        $this->contexts = null;
    }

    public function mergeCollection(ConfigurationCollection $collection): void
    {
        $this->configurations = array_merge($this->configurations, $collection->getConfigurationsArray());
    }

    public function getConfigurationsArray(): array
    {
        return $this->configurations;
    }

    public function getContextCollection(): BuildContextCollection
    {
        if (is_null($this->contexts)) {
            $contexts = [];
            $fileStructure = new FileStructure();
            foreach ($this->configurations as $configuration) {
                $projectDirectory = $configuration->getDirectory();
                if (!$fileStructure->hasProject($projectDirectory))
                    $fileStructure->scanDirectory($projectDirectory);

                $projectFiles = new ProjectFiles($fileStructure->getProjectFiles($projectDirectory), $configuration);
                $contexts[] = ContextBuilder::build($projectFiles, $configuration);
            }
            $this->contexts = new BuildContextCollection($contexts);
        }

        return $this->contexts;
    }

    public function getPackages(): array
    {
        $packages = [];
        foreach ($this->configurations as $configuration)
            foreach ($configuration->getProjectDependencies()->getPackages() as $name => $version)
                $packages[$name] = $version;

        return $packages;
    }
}