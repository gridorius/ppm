<?php

namespace Builder;

use ArrayIterator;
use Assembly\Resources;
use Builder\Configuration\Contracts\IManifestBuilder;
use Builder\Configuration\Contracts\IProjectConfiguration;
use Builder\Contracts\IProjectBuilder;
use Builder\Contracts\IProjectStructure;
use Phar;
use Utils\FileUtils;
use Utils\ReplaceUtils;

class ProjectBuilder implements IProjectBuilder
{
    public function build(IProjectStructure $projectStructure, string $outDirectory): void
    {
        $projectConfiguration = $projectStructure->getProjectInfo()->getConfiguration();
        $phar = $this->createPhar($outDirectory, $projectConfiguration);
        $phar->startBuffering();
        $phar->buildFromIterator(new ArrayIterator($projectStructure->getPharFilesIterator()));
        FileUtils::moveFiles($projectStructure->getOuterFiles(), $outDirectory);
        $this->makeJsonManifest($phar, $projectStructure->getManifestBuilder());
        $this->makePhpManifest($phar, $projectStructure->getManifestBuilder());
        $this->makeStub($phar, $projectConfiguration);
        $phar->stopBuffering();

        if (!empty($projectConfiguration->hasEntrypoint()))
            $this->makeExecutableFile($outDirectory, $projectConfiguration);
    }

    public function makeStub(Phar $phar, IProjectConfiguration $projectConfiguration)
    {
        $phar->setStub(
            preg_replace(
                "/PROJECT_NAME/",
                $projectConfiguration->getName(),
                $projectConfiguration->hasStub()
                    ? $projectConfiguration->getStubContent()
                    : $this->getFileOrResource('templates/stub.php')
            )
        );
    }

    protected function makeExecutableFile(string $outDirectory, IProjectConfiguration $configuration)
    {
        $entrypointData = explode('::', $configuration->getEntrypoint());
        $entrypointClass = $entrypointData[0];
        $entrypointMethod = $entrypointData[1] ?? 'main';
        $runnerContent = ReplaceUtils::replace([
            "PROJECT_NAME",
            "ENTRYPOINT_CLASS",
            "ENTRYPOINT_METHOD",
        ],
            [
                $configuration->getName(),
                $entrypointClass,
                $entrypointMethod,
            ],
            $this->getFileOrResource('templates/runner.php'));

        file_put_contents($outDirectory . DIRECTORY_SEPARATOR . $configuration->getRunner() . '.php', $runnerContent);
    }

    protected function getFileOrResource(string $relativePath): string
    {
        if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $relativePath))
            return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $relativePath);
        else
            return Resources::get($relativePath)->getContent();
    }

    protected function makeJsonManifest(Phar $phar, IManifestBuilder $manifestBuilder)
    {
        $phar->addFromString(
            'manifest.json',
            json_encode($manifestBuilder->buildForJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    protected function makePhpManifest(Phar $phar, IManifestBuilder $manifestBuilder)
    {
        $manifest = $manifestBuilder->buildForPhp();
        $phar->addFromString('manifest.php', "<?php\n return " . var_export($manifest, true) . ";\n");
    }

    protected function createPhar(string $outDirectory, IProjectConfiguration $projectConfiguration): Phar
    {
        if (!is_dir($outDirectory))
            mkdir($outDirectory, 0755, true);

        return new Phar($outDirectory . DIRECTORY_SEPARATOR . $projectConfiguration->getName() . '.phar');
    }
}