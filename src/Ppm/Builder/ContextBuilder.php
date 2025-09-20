<?php

namespace Ppm\Builder;

use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\Manifest;

class ContextBuilder
{
    /**
     * build project context
     *
     * @param ProjectFiles $projectFiles
     * @param Configuration $configuration
     * @return BuildContext
     */
    public static function build(ProjectFiles $projectFiles, Configuration $configuration): BuildContext
    {
        $manifest = new Manifest($configuration);
        $innerFiles = [];
        $outerFiles = [];
        static::prepareTypedFiles($projectFiles, $manifest, $innerFiles);
        static::prepareMovedFiles($projectFiles, $outerFiles);
        static::prepareResources($projectFiles, $manifest, $innerFiles);
        static::prepareIncludes($projectFiles, $manifest, $innerFiles);
        return new BuildContext($projectFiles, $configuration, $manifest, $innerFiles, $outerFiles);
    }

    private static function prepareTypedFiles(ProjectFiles $filter, Manifest $manifest, array &$innerFiles): void
    {
        $types = [];
        foreach ($filter->getTypeFiles() as $path => $relativePath) {
            $foundTypes = EntityFinder::findByTokens($path);
            foreach ($foundTypes as $type) {
                $localPath = 'types/' . preg_replace("/\\\\/", '.', $type) . '.php';
                $types[$type] = $localPath;
                $innerFiles[$localPath] = $path;
            }
        }
        $manifest->setTypes($types);
    }

    private static function prepareMovedFiles(ProjectFiles $filter, array &$outerFiles): void
    {
        foreach ($filter->getFiles() as $realPath => $relativePath)
            $outerFiles[$relativePath] = $realPath;
    }

    private static function prepareResources(ProjectFiles $filter, Manifest $manifest, array &$innerFiles): void
    {
        $resources = [];
        foreach ($filter->getResources() as $path => $relativePath) {
            $innerPath = 'resources/' . $relativePath;
            $resources[$relativePath] = $innerPath;
            $innerFiles[$innerPath] = $path;
        }
        $manifest->setResources($resources);
    }

    private static function prepareIncludes(ProjectFiles $filter, Manifest $manifest, array &$innerFiles): void
    {
        $includes = [];
        foreach ($filter->getIncludes() as $path => $relativePath) {
            $localPath = 'includes/' . pathinfo($relativePath, PATHINFO_BASENAME);
            $includes[] = $localPath;
            $innerFiles[$localPath] = $path;
        }
        $manifest->setIncludes($includes);
    }
}