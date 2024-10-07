<?php

namespace Ppm\Packages\Common;


use Ppm\Builder\Configuration\Configuration;

class MetadataUtil
{
    const METADATA_FILE_NAME = '__metadata.php';

    public static function createFromConfigurationCollection(Configuration $configuration): array
    {
        $configurationCollection = $configuration->buildConfigurationCollection();
        return [
            'name' => $configuration->getName(),
            'version' => $configuration->getVersion(),
            'author' => $configuration->getAuthor(),
            'description' => $configuration->getDescription(),
            'depends' => $configurationCollection->getPackages(),
            'hashes' => []
        ];
    }

    public static function createMetadataFile(string $packageDirectory, array $metadata): void
    {
        file_put_contents(
            $packageDirectory . DIRECTORY_SEPARATOR . MetadataUtil::METADATA_FILE_NAME,
            "<?php\nreturn " . var_export($metadata, true) . ';');
    }

    public static function getPackageMetadata(string $path): Metadata
    {
        return new Metadata(include 'phar://' . $path . '/' . static::METADATA_FILE_NAME);
    }

    public static function getDirectoryMetadata(string $path): Metadata
    {
        return new Metadata(include $path . DIRECTORY_SEPARATOR . MetadataUtil::METADATA_FILE_NAME);
    }
}