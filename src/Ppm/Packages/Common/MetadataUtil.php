<?php

namespace Ppm\Packages\Common;


use Ppm\Builder\Configuration\Configuration;
use Ppm\Framework\Utils\ArrayUtils;

class MetadataUtil
{
    const METADATA_FILE_NAME = '__metadata.php';

    public static function createFromConfigurationCollection(Configuration $configuration): array
    {
        $configurationCollection = $configuration->buildConfigurationCollection();
        $projectInfo = $configuration->getProjectInfo();
        return [
            'name' => $projectInfo->getName(),
            'version' => $projectInfo->getVersion(),
            'author' => $projectInfo->getAuthor(),
            'description' => $projectInfo->getDescription(),
            'depends' => $configurationCollection->getPackages(),
            'hashes' => [],
            'hashSum' => ''
        ];
    }

    public static function createMetadataFile(string $packageDirectory, array $metadata): void
    {
        file_put_contents(
            $packageDirectory . DIRECTORY_SEPARATOR . MetadataUtil::METADATA_FILE_NAME,
            "<?php\nreturn " . ArrayUtils::export($metadata) . ';');
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