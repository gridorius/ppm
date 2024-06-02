<?php

namespace Builder;

use ArrayIterator;
use Phar;
use Resources;
use Utils\FileUtils;
use Utils\ReplaceUtils;

class ProjectBuilder
{
    protected string $projectDir;
    protected ProjectStructure $structure;

    protected array $manifest;

    public function __construct(string $projectDir, ProjectStructure $structure)
    {
        $this->projectDir = $projectDir;
        $this->structure = $structure;
        $this->manifest = $structure->manifest;
    }

    public function build(string $outDirectory)
    {
        $phar = $this->createPhar($outDirectory);
        $phar->startBuffering();
        $phar->buildFromIterator(new ArrayIterator($this->structure->innerMove));
        FileUtils::moveFiles($this->structure->outerMove, $outDirectory);
        $this->makeManifest($phar);
        $this->makeStub($phar);
        $phar->stopBuffering();

        if (!empty($this->structure->entrypoint))
            $this->makeExecutableFile($outDirectory);
    }

    public function makeStub(Phar $phar)
    {
        $phar->setStub(
            preg_replace(
                "/PROJECT_NAME/",
                $this->manifest['name'],
                $this->getFileOrResource('templates/stub.php')
            )
        );
    }

    protected function makeExecutableFile(string $outDirectory)
    {
        $entrypointData = explode('::', $this->structure->entrypoint);
        $entrypointClass = $entrypointData[0];
        $entrypointMethod = $entrypointData[1] ?? 'main';
        $executorContent = ReplaceUtils::replace([
            "PROJECT_NAME",
            "ENTRYPOINT_CLASS",
            "ENTRYPOINT_METHOD",
        ],
            [
                $this->manifest['name'],
                $entrypointClass,
                $entrypointMethod,
            ],
            $this->getFileOrResource('templates/executor.php'));

        file_put_contents($outDirectory . DIRECTORY_SEPARATOR . $this->structure->runner . '.php', $executorContent);
    }

    protected function getFileOrResource(string $relativePath): string
    {
        if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $relativePath))
            return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $relativePath);
        else
            return Resources::get($relativePath)->getContent();
    }

    protected function makeManifest(Phar $phar)
    {
        $phar->addFromString(
            'manifest.json',
            json_encode($this->manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    protected function createPhar(string $outDirectory): Phar
    {
        if (!is_dir($outDirectory))
            mkdir($outDirectory, 0755, true);

        return new Phar($outDirectory . DIRECTORY_SEPARATOR . $this->manifest['name'] . '.phar');
    }
}