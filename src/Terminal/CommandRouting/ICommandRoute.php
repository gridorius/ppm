<?php

namespace Terminal\CommandRouting;

interface ICommandRoute
{
    public function getDescription(): string;

    public function setDescription(string $description): void;

    public function setOptions(array $options): ICommandRoute;

    public function handle($argv): void;
}