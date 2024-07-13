<?php

namespace Builder\Actions;

interface IAction
{
    public function run(): void;

    public function setDirectories(string $buildDirectory, string $outDirectory): void;
}