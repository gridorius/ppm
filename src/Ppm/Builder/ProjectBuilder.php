<?php

namespace Ppm\Builder;

use ArrayIterator;
use Phar;
use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\Manifest;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Resources\Resources;
use Ppm\Framework\Utils\StringUtils;
use Utils\FileUtils;
use Utils\ReplaceUtils;

class ProjectBuilder
{
    public function build(BuildContext $context, string $outDirectory): void
    {
        $directory = (new Directory($outDirectory))->create();
        $configuration = $context->getConfiguration();
        $actions = $configuration->getActions();
        $actions->runBeforeBuild($configuration->getDirectory(), $directory->getPath());
        $phar = $directory->createPhar($configuration->getName());
        $phar->startBuffering();
        $phar->buildFromIterator(new ArrayIterator($context->getInnerFiles()));
        $directory->copyFiles($context->getOuterFiles());
        $this->makePhpManifest($phar, $context->getManifest());
        $this->makeStub($phar, $configuration);
        $phar->stopBuffering();

        if (!empty($configuration->hasEntrypoint()))
            $this->makeExecutableFile($outDirectory, $configuration);

        $actions->runAfterBuild($configuration->getDirectory(), $outDirectory);
    }

    public function makeStub(Phar $phar, Configuration $configuration): void
    {
        $phar->setStub(
            preg_replace(
                Constants::PROJECT_NAME_REGEX_PATTERN,
                $configuration->getName(),
                $configuration->hasStub()
                    ? file_get_contents($configuration->getStubPath())
                    : $this->getFileOrResource(Constants::STUB_TEMPLATE_PATH)
            )
        );
    }

    protected function makeExecutableFile(string $outDirectory, Configuration $configuration): void
    {
        $entrypointData = explode('::', $configuration->getEntrypoint());
        $entrypointClass = $entrypointData[0];
        $entrypointMethod = $entrypointData[1] ?? Constants::DEFAULT_ENTRYPOINT_METHOD;
        $runnerContent = StringUtils::replace([
            Constants::REPLACE_PROJECT_NAME,
            Constants::REPLACE_ENTRYPOINT_CLASS,
            Constants::REPLACE_ENTRYPOINT_METHOD,
        ],
            [
                $configuration->getName(),
                $entrypointClass,
                $entrypointMethod,
            ],
            $this->getFileOrResource(Constants::RUNNER_TEMPLATE_PATH));

        file_put_contents($outDirectory . DIRECTORY_SEPARATOR . $configuration->getRunner() . '.php', $runnerContent);
    }

    protected function getFileOrResource(string $relativePath): string
    {
        if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $relativePath))
            return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $relativePath);
        else
            return Resources::get($relativePath)->getContent();
    }

    protected function makePhpManifest(Phar $phar, Manifest $manifest): void
    {
        $phar->addFromString(
            Constants::MANIFEST_FILE_NAME_PHP,
            "<?php\n return " . var_export($manifest->toArray(), true) . ";\n"
        );
    }
}