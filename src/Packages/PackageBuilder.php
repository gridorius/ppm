<?php

namespace Packages;

use ArrayIterator;
use Builder\BuildManager;
use Phar;
use Utils\PathUtils;

class PackageBuilder
{
    protected string $packagesDirectory;
    protected string $tmpDirectory;

    public function __construct(string $packagesDirectory, string $tmpDirectory)
    {
        $this->packagesDirectory = $packagesDirectory;
        $this->tmpDirectory = $tmpDirectory;
        if (!is_dir($packagesDirectory))
            mkdir($packagesDirectory, 0755, true);
    }

    public function buildPackage(string $projectPath): string
    {
        $outDirectory = $this->tmpDirectory . DIRECTORY_SEPARATOR . '_package_' . time();
        $proj = PathUtils::findProj($projectPath);
        $director = new BuildManager($proj);
        $director->build($outDirectory);
        $scanner = $director->getScanner();

        $configuration = $scanner->getConfiguration();
        $name = $configuration['name'];
        $version = $configuration['version'];
        $packageManifest = [
            'name' => $name,
            'version' => $version,
            'author' => $configuration['author'] ?? null,
            'description' => $configuration['description'] ?? null,
            'depends' => $scanner->getPackages(),
            'hashes' => []
        ];

        $prefixLength = strlen($outDirectory) + 1;
        $packageFiles = [];
        foreach (glob($outDirectory . DIRECTORY_SEPARATOR . '*') as $path) {
            $localPath = substr($path, $prefixLength);
            $packageFiles[$localPath] = $path;
            $packageManifest['hashes'][$localPath] = hash_file('sha256', $path);
        }
        $packageName = PathUtils::getPackageName($name, $version);
        $packagePath = $this->packagesDirectory . DIRECTORY_SEPARATOR . $packageName;
        if (file_exists($packagePath))
            unlink($packagePath);
        $phar = new Phar($this->packagesDirectory . DIRECTORY_SEPARATOR . $packageName);
        $phar->startBuffering();
        $phar->buildFromIterator(new ArrayIterator($packageFiles));
        $phar->setMetadata($packageManifest);
        $phar->stopBuffering();
        foreach ($packageFiles as $path) {
            unlink($path);
        }
        rmdir($outDirectory);
        echo "Package {$packageName} built\n";
        return $packagePath;
    }
}