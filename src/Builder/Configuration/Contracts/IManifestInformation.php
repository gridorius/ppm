<?php

namespace Builder\Configuration\Contracts;

interface IManifestInformation
{
    public function getTypesCount(): int;

    public function getResourcesCount(): int;

    public function getIncludesCount(): int;

    public function getDependsCount(): int;
}