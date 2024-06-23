<?php

namespace Terminal\CommandRouting;

interface ICommandRoute
{
    public function getDescription(): string;

    public function setDescription(string $description): void;

    public function setDefinedOptions(array $options): ICommandRoute;

    public function handle($argv): void;
}