<?php

namespace Ppm\Packages;

use Phar;
use Ppm\Builder\BuildManager;
use Ppm\Builder\Configuration\Configuration;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\File;
use Ppm\Framework\Filesystem\TmpManager;
use Ppm\Packages\Common\MetadataUtil;
use Ppm\Packages\Common\Storage\PackagesStorage;

class PackageBuilder
{
    private BuildManager $buildManager;
    private PackagesStorage $packages;
    private TmpManager $tmp;

    public function __construct(PackagesStorage $packages, TmpManager $tmp)
    {
        $this->packages = $packages;
        $this->buildManager = new BuildManager();
        $this->tmp = $tmp;
    }

    public function build(string $pathToProjectFile): void
    {
        $mainConfiguration = new Configuration($pathToProjectFile);
        $configurationCollection = $mainConfiguration->buildConfigurationCollection();
        $tmpDirectory = $this->tmp->createTmpDirectory();
        $this->buildManager->buildFromConfigurationCollection($configurationCollection, $tmpDirectory->getPath());
        $metadata = $this->createMetadata($mainConfiguration, $tmpDirectory);
        MetadataUtil::createMetadataFile($tmpDirectory->getPath(), $metadata);
        $packed = $this->pack($tmpDirectory, $metadata);
        $this->packages->import($packed->getPath());
        $tmpDirectory->delete();

        echo "Package {$mainConfiguration->getProjectInfo()->getName()}:{$mainConfiguration->getProjectInfo()->getVersion()} built\n";
    }

    public function createMetadata(Configuration $mainConfiguration, Directory $tmpDirectory): array
    {
        $packageMetadata = MetadataUtil::createFromConfigurationCollection($mainConfiguration);
        $prefixLength = strlen($tmpDirectory->getPath()) + 1;
        foreach ($tmpDirectory->glob(DIRECTORY_SEPARATOR . '*') as $path) {
            $relativePath = substr($path, $prefixLength);
            $packageMetadata['hashes'][$relativePath] = hash_file('sha256', $path);
        }
        $packageMetadata['hashSum'] = hash('sha256', implode('', $packageMetadata['hashes']));
        return $packageMetadata;
    }

    public function pack(Directory $directory, array $metadata): File
    {
        $file = $this->tmp->getTmpFile('phar');
        $phar = new Phar($file->getPath());
        $phar->startBuffering();
        $phar->buildFromDirectory($directory->getPath());
        $phar->stopBuffering();
        return $file;
    }
}