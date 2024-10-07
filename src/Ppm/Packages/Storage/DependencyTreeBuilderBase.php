<?php

namespace Ppm\Packages\Storage;

abstract class DependencyTreeBuilderBase
{
    protected PackageStorageBase $storage;

    public function buildPackagesTree(array $packages, DependencyTree &$tree = null): DependencyTree
    {
        if (is_null($tree))
            $tree = new DependencyTree();

        foreach ($packages as $name => $version) {
            $lastVersion = $this->storage->findLastVersion($name, $version);
            if (!is_null($lastVersion)) {
                $tree->addFound($name, $lastVersion);
                $depends = $this->getDepends($name, $lastVersion);
                if ($depends) $this->buildPackagesTree($depends, $tree);
            } else
                $tree->addNotFound($name, $version);
        }
        return $tree;
    }

    abstract protected function getDepends(string $name, string $version): ?array;
}