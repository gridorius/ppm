<?php

namespace PpmRegistry\Contracts;

interface ILocalPackage extends IPackage
{
    public function getPath(): string;

    public function getDepends(): array;
}