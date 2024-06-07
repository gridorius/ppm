<?php

namespace Builder\Configuration\Contracts;

interface IManifestInformation
{
    public function getTypeCount(): int;

    public function getResourcesCount(): int;

    public function getIncludesCount(): int;

    public function getDependsCount(): int;
}