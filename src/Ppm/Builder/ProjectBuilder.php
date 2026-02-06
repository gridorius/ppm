<?php

namespace Ppm\Builder;

use ArrayIterator;
use Exception;
use Phar;
use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\Manifest;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Resources\Resources;
use Ppm\Framework\Utils\ArrayUtils;
use Ppm\Framework\Utils\StringUtils;

class ProjectBuilder
{
    /**
     * build project from BuildContext
     *
     * @param BuildContext $context
     * @param string $outDirectory
     * @return void
     * @throws Exception
     */
    public function build(BuildContext $context, string $outDirectory): void
    {
        $directory = (new Directory($outDirectory))->create();
        $configuration = $context->getConfiguration();
        $phar = $directory->createPhar($configuration->getProjectInfo()->getName());
        $phar->startBuffering();
        $phar->buildFromIterator(new ArrayIterator($context->getInnerFiles()));
        $directory->copyFiles($context->getOuterFiles());
        $this->makePhpManifest($phar, $context->getManifest());
        $this->makeStub($phar, $configuration);
        $phar->stopBuffering();

        if (!empty($configuration->hasEntrypoint()))
            $this->makeExecutableFile($outDirectory, $configuration);
    }

    public function update(BuildContext $context, array $relations, array $removedFiles, string $outDirectory): void
    {
        $directory = (new Directory($outDirectory))->create();
        $configuration = $context->getConfiguration();
        $phar = $directory->getPhar($configuration->getProjectInfo()->getName());
        $phar->startBuffering();

        foreach ($removedFiles as $relativePath) {
            if (key_exists($relativePath, $relations)) {
                $data = $relations[$relativePath];
                switch ($data[0]) {
                    case 'type':
                    case 'resource':
                    case 'include':
                        $phar->delete($data[2]);
                    case 'file':
                        unlink($outDirectory . DIRECTORY_SEPARATOR . $relativePath);
                        break;
                }
            }
        }

        $phar->buildFromIterator(new ArrayIterator($context->getInnerFiles()));
        $directory->copyFiles($context->getOuterFiles());
        $this->makePhpManifest($phar, $context->getManifest());
        $this->makeStub($phar, $configuration);
        $phar->stopBuffering();

        if (!empty($configuration->hasEntrypoint()))
            $this->makeExecutableFile($outDirectory, $configuration);
    }

    /**
     * add stub to Phar
     *
     * @param Phar $phar
     * @param Configuration $configuration
     * @return void
     * @throws Exception
     */
    public function makeStub(Phar $phar, Configuration $configuration): void
    {
        $phar->setStub(
            preg_replace(
                Constants::PROJECT_NAME_REGEX_PATTERN,
                $configuration->getProjectInfo()->getName(),
                $configuration->hasStub()
                    ? file_get_contents($configuration->getStubPath())
                    : $this->getFileOrResource(Constants::STUB_TEMPLATE_PATH)
            )
        );
    }

    /**
     * create executable from configuration section "runner"
     *
     * @param string $outDirectory
     * @param Configuration $configuration
     * @return void
     */
    protected function makeExecutableFile(string $outDirectory, Configuration $configuration): void
    {
        $entrypointData = explode('::', $configuration->getEntrypoint());
        $entrypointClass = $entrypointData[0];
        $entrypointMethod = $entrypointData[1] ?? Constants::DEFAULT_ENTRYPOINT_METHOD;
        $runnerContent = StringUtils::replace(
            $this->getFileOrResource(Constants::RUNNER_TEMPLATE_PATH),
            [
                Constants::REPLACE_PROJECT_NAME => $configuration->getProjectInfo()->getName(),
                Constants::REPLACE_ENTRYPOINT_CLASS => $entrypointClass,
                Constants::REPLACE_ENTRYPOINT_METHOD => $entrypointMethod,
            ]
        );

        file_put_contents($outDirectory . DIRECTORY_SEPARATOR . $configuration->getRunner() . '.php', $runnerContent);
    }

    protected function getFileOrResource(string $relativePath): string
    {
        if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $relativePath))
            return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $relativePath);
        else
            return Resources::get($relativePath)->getContent();
    }

    /**
     * add php manifest to phar
     *
     * @param Phar $phar
     * @param Manifest $manifest
     * @return void
     */
    protected function makePhpManifest(Phar $phar, Manifest $manifest): void
    {
        $phar->addFromString(
            Constants::MANIFEST_FILE_NAME_PHP,
            "<?php\n return " . ArrayUtils::export($manifest->toArray()) . ";\n"
        );
    }
}