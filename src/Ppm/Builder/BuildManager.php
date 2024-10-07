<?php

namespace Ppm\Builder;

use Exception;
use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\ConfigurationCollection;
use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Terminal\ShellStyleParser;
use Ppm\Framework\Types\Timer;

class BuildManager
{
    public static function buildFromConfigurationCollection(ConfigurationCollection $configurationCollection, string $outDirectory): void
    {
        try {
            $timer = new Timer();
            static::buildProjects($configurationCollection, $outDirectory);
            $passed = $timer->getPassed();
            echo "Build is completed in {$passed}s\n";
            echo "Output directory: {$outDirectory}\n";
        } catch (Exception $exception) {
            echo "Build failed\n";
            echo $exception->getMessage() . "\n";
            echo $exception->getTraceAsString() . "\n";
            exit(1);
        }
    }

    public static function build(string $pathToProjectFile, string $outDirectory): void
    {
        $mainConfiguration = new Configuration($pathToProjectFile);
        $configurationCollection = $mainConfiguration->buildConfigurationCollection();
        static::buildFromConfigurationCollection($configurationCollection, $outDirectory);
    }

    public static function AddAssemblyPhar(string $outDirectory): void
    {
        copy(Path::assemblyCombine(Constants::FRAMEWORK_PHAR_NAME), $outDirectory . DIRECTORY_SEPARATOR . Constants::FRAMEWORK_PHAR_NAME);
    }

    protected static function buildProjects(ConfigurationCollection $configurationCollection, string $outDirectory): void
    {
        $projectBuilder = new ProjectBuilder();
        foreach ($configurationCollection->buildProjectsContexts() as $context) {
            $timer = new Timer();
            $projectBuilder->build($context, $outDirectory);
            static::showBuildLog($timer->getPassed(), $context);
        }
    }

    private static function showBuildLog(string $passed, BuildContext $context): void
    {
        $configuration = $context->getConfiguration();
        $manifest = $context->getManifest();
        echo ShellStyleParser::style("<s style='b,green'>{$configuration->getName()}</s>:<s style='blue'>{$configuration->getVersion()}</s> built in {$passed}s\n");
        echo ShellStyleParser::style("\tTypes: <s style='green'>{$manifest->getTypesCount()}</s>"
            . "\tResources: <s style='green'>{$manifest->getResourcesCount()}</s>"
            . "\tIncludes: <s style='green'>{$manifest->getIncludesCount()}</s>"
            . "\tDepends: <s style='green'>{$manifest->getDependsCount()}</s>\n");
    }
}