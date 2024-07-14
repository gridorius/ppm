<?php

namespace Packages;

use Builder\Configuration\Contracts\IConfigurationCollection;

class Metadata
{
    const METADATA_FILE_NAME = '__metadata.php';

    public static function createFromConfigurationCollection(IConfigurationCollection $configurationCollection): array
    {
        $configuration = $configurationCollection->getMainConfiguration();
        return [
            'name' => $configuration->getName(),
            'version' => $configuration->getVersion(),
            'author' => $configuration->getAuthor(),
            'description' => $configuration->getDescription(),
            'depends' => $configurationCollection->getDepends(),
            'commands' => $configuration->getCommands(),
            'hashes' => []
        ];
    }

    public static function createMetadataFile(string $packageDirectory, array $metadata): void
    {
        file_put_contents(
            $packageDirectory . DIRECTORY_SEPARATOR . Metadata::METADATA_FILE_NAME,
            "<?php\nreturn " . var_export($metadata, true) . ';');
    }
}