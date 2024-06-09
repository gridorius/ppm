<?php

namespace Builder;

use Assembly\Utils;
use Builder\Configuration\ConfigurationCollector;
use Builder\Configuration\Contracts\IConfigurationCollection;
use Builder\Configuration\Contracts\IConfigurationCollector;
use Builder\Contracts\IBuildManager;
use Builder\Contracts\IProjectStructure;
use Exception;
use Terminal\ShellStyleParser;
use Utils\Timer;

class BuildManager implements IBuildManager
{
    protected IConfigurationCollector $collector;

    public function __construct()
    {
        $this->collector = new ConfigurationCollector();
    }

    public function buildFromConfigurationCollection(IConfigurationCollection $configurationCollection, string $outDirectory): void
    {
        try {
            $timer = new Timer();
            $configurationCollection->setVersionIfEmpty($configurationCollection->getMainConfiguration()->getVersion());
            $this->buildProjects($configurationCollection, $outDirectory);
            $passed = $timer->getPassed();
            echo "Build is completed in {$passed}s\n";
            echo "Output directory: {$outDirectory}\n";
        } catch (Exception $exception) {
            echo "Build failed\n";
            echo $exception->getMessage() . "\n";
            exit(1);
        }
    }

    public function build(string $pathToProjectFile, string $outDirectory): void
    {
        $configurationCollection = $this->collector->collect($pathToProjectFile);
        $this->buildFromConfigurationCollection($configurationCollection, $outDirectory);
    }

    public function AddAssemblyPhar(string $outDirectory): void
    {
        copy(Utils::path('Assembly.phar'), $outDirectory . DIRECTORY_SEPARATOR . 'Assembly.phar');
    }

    protected function buildProjects(IConfigurationCollection $configurationCollection, string $outDirectory): void
    {
        $projectStructureBuilder = new ProjectStructureBuilder();
        $projectBuilder = new ProjectBuilder();
        foreach ($configurationCollection->buildProjectInfos() as $projectInfo) {
            $timer = new Timer();
            $structure = $projectStructureBuilder->build($projectInfo);
            $projectBuilder->build($structure, $outDirectory);
            $this->showBuildLog($timer->getPassed(), $structure);
        }
    }

    private function showBuildLog(string $passed, IProjectStructure $projectStructure)
    {
        $configuration = $projectStructure->getProjectInfo()->getConfiguration();
        $manifestInfo = $projectStructure->getManifestInfo();
        echo ShellStyleParser::style("<s style='b,green'>{$configuration->getName()}</s>:<s style='blue'>{$configuration->getVersion()}</s> built in {$passed}s\n");
        echo ShellStyleParser::style("\tTypes: <s style='green'>{$manifestInfo->getTypeCount()}</s>"
            . "\tResources: <s style='green'>{$manifestInfo->getResourcesCount()}</s>"
            . "\tIncludes: <s style='green'>{$manifestInfo->getIncludesCount()}</s>"
            . "\tDepends: <s style='green'>{$manifestInfo->getDependsCount()}</s>\n");
    }
}