<?php

namespace Packages\Contracts;

interface IPackage
{
    public function getName(): string;

    public function getVersion(): string;
}