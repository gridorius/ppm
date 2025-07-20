<?php

namespace Ppm\Builder;

use Exception;
use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\ConfigurationCollection;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Terminal\ShellStyleParser;
use Ppm\Framework\Types\Timer;

class BuildManager
{
    /**
     * build project from ConfigurationCollection
     *
     * @param ConfigurationCollection $configurationCollection
     * @param string $outDirectory
     * @return void
     */
    public static function buildFromConfigurationCollection(ConfigurationCollection $configurationCollection, string $outDirectory): void
    {
        try {
            $timer = new Timer();
            static::buildProjects($configurationCollection, $outDirectory);
            $passed = $timer->getFormatPassed();
            echo "Build is completed in {$passed}s\n";
            echo "Output directory: {$outDirectory}\n";
        } catch (Exception $exception) {
            echo "Build failed\n";
            echo $exception->getMessage() . "\n";
            echo $exception->getTraceAsString() . "\n";
            exit(1);
        }
    }

    /**
     * build project from path
     *
     * @param string $pathToProjectFile
     * @param string $outDirectory
     * @return void
     */
    public static function build(string $pathToProjectFile, string $outDirectory): void
    {
        $configurationCollection = ConfigurationCollection::from($pathToProjectFile);
        static::buildFromConfigurationCollection($configurationCollection, $outDirectory);
    }

    /**
     * add framework phar to build directory
     *
     * @param string $outDirectory
     * @return void
     */
    public static function AddFrameworkPhar(string $outDirectory): void
    {
        copy(Path::assemblyCombine(Constants::FRAMEWORK_PHAR_NAME), $outDirectory . DIRECTORY_SEPARATOR . Constants::FRAMEWORK_PHAR_NAME);
    }

    protected static function buildProjects(ConfigurationCollection $configurationCollection, string $outDirectory): void
    {
        $projectBuilder = new ProjectBuilder();
        $contexts = $configurationCollection->getContextCollection()->toArray();
        $directory = (new Directory($outDirectory))->create();

        foreach ($contexts as $context)
            $context
                ->getConfiguration()
                ->getActions()
                ->runBeforeBuild($context->getConfiguration()->getDirectory(), $directory->getPath());

        foreach ($contexts as $context) {
            $timer = new Timer();
            $projectBuilder->build($context, $outDirectory);
            static::showBuildLog($timer->getFormatPassed(), $context);
        }

        foreach ($contexts as $context)
            $context
                ->getConfiguration()
                ->getActions()
                ->runAfterBuild($context->getConfiguration()->getDirectory(), $directory->getPath());
    }

    private static function showBuildLog(string $passed, BuildContext $context): void
    {
        $configuration = $context->getConfiguration();
        $manifest = $context->getManifest();
        $projectInfo = $configuration->getProjectInfo();
        echo ShellStyleParser::style("<s b green>{$projectInfo->getName()}</s>:<s blue>{$projectInfo->getVersion()}</s> built in {$passed}s\n");
        echo ShellStyleParser::style("\tTypes: <s green>{$manifest->getTypesCount()}</s>"
            . "\tResources: <s green>{$manifest->getResourcesCount()}</s>"
            . "\tIncludes: <s green>{$manifest->getIncludesCount()}</s>"
            . "\tDepends: <s green>{$manifest->getDependsCount()}</s>\n");
    }
}