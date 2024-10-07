<?php

namespace Ppm\Builder;

use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\Manifest;

class ContextBuilder
{
    public static function build(ProjectFiles $projectFiles, Configuration $configuration): BuildContext
    {
        $manifest = new Manifest($configuration);
        $innerFiles = [];
        $outerFiles = [];
        static::findTypeFiles($projectFiles, $manifest, $innerFiles);
        static::findMoveFiles($projectFiles, $outerFiles);
        static::findResources($projectFiles, $manifest, $innerFiles);
        static::findIncludes($projectFiles, $manifest, $innerFiles);
        return new BuildContext($configuration, $manifest, $innerFiles, $outerFiles);
    }

    private static function findTypeFiles(ProjectFiles $filter, Manifest $manifest, array &$innerFiles): void
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

    private static function findMoveFiles(ProjectFiles $filter, array &$outerFiles): void
    {
        foreach ($filter->getFiles() as $realPath => $relativePath)
            $outerFiles[$relativePath] = $realPath;
    }

    private static function findResources(ProjectFiles $filter, Manifest $manifest, array &$innerFiles): void
    {
        $resources = [];
        foreach ($filter->getResources() as $path => $relativePath) {
            $innerPath = 'resources/'. $relativePath;
            $resources[$relativePath] = $innerPath;
            $innerFiles[$innerPath] = $path;
        }
        $manifest->setResources($resources);
    }

    private static function findIncludes(ProjectFiles $filter, Manifest $manifest, array &$innerFiles): void
    {
        $includes = [];
        foreach ($filter->getIncludes() as $path => $relativePath) {
            $localPath = static::makeInnerPath($path);
            $includes[] = $localPath;
            $innerFiles[$localPath] = $path;
        }
        $manifest->setIncludes($includes);
    }

    private static function makeInnerPath(string $path): string
    {
        return hash_file('sha256', $path) . '.' . pathinfo($path, PATHINFO_EXTENSION);
    }
}