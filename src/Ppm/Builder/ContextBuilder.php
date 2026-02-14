<?php

namespace Ppm\Builder;

use Ppm\Builder\Configuration\Configuration;
use Ppm\Builder\Configuration\Manifest;

class ContextBuilder
{
    public static function prepareTypedFiles(ProjectFiles $filter, Manifest $manifest, array &$innerFiles): void
    {
        $types = [];
        foreach ($filter->getTypeFiles() as $path => $relativePath) {
            $foundTypes = EntityFinder::findByTokens($path);
            foreach ($foundTypes as $type) {
                $localPath = 'types/' . preg_replace("/\\\\/", '.', $type) . '.php';
                $types[$type] = $localPath;
                $innerFiles[$localPath] = $path;
                $manifest->setFileRelation($relativePath, 'type', $type, $localPath);
            }
        }
        $manifest->setTypes($types);
    }

    public static function prepareMovedFiles(ProjectFiles $filter, Manifest $manifest, array &$outerFiles): void
    {
        foreach ($filter->getFiles() as $realPath => $relativePath) {
            $outerFiles[$relativePath] = $realPath;
            $manifest->setFileRelation($relativePath, 'file');
        }
    }

    public static function prepareResources(ProjectFiles $filter, Manifest $manifest, array &$innerFiles): void
    {
        $resources = [];
        foreach ($filter->getResources() as $path => $relativePath) {
            $innerPath = 'resources/' . $relativePath;
            $resources[$relativePath] = $innerPath;
            $innerFiles[$innerPath] = $path;
            $manifest->setFileRelation($relativePath, 'resource', $relativePath, $innerPath);
        }
        $manifest->setResources($resources);
    }

    public static function prepareIncludes(ProjectFiles $filter, Manifest $manifest, array &$innerFiles): void
    {
        $includes = [];
        foreach ($filter->getIncludes() as $path => $relativePath) {
            $localPath = 'includes/' . pathinfo($relativePath, PATHINFO_BASENAME);
            $includes[$localPath] = $localPath;
            $innerFiles[$localPath] = $path;
            $manifest->setFileRelation($relativePath, 'include', $localPath, $localPath);
        }
        $manifest->setIncludes($includes);
    }
}