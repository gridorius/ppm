<?php

namespace PpmRegistry\Contracts;

interface ILocalManager
{
    public function exist(string $name, string $concreteVersion): bool;

    public function get(string $name, string $findVersion): ?ILocalPackage;

    public function findLocalPackage(string $name, string $version): string;

    public function getLocalPath(string $name, string $version): string;

    public function save(string $name, string $version, string $content): ILocalPackage;

    public function scanPackageVersions(string $packageName): void;

    public function toArray(): array;
}