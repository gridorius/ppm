<?php

namespace Terminal\CommandRouting;

interface ICommandRoute
{
    public function getDescription(): string;

    public function setDescription(string $description): ICommandRoute;

    public function setDefinedOptions(array $options): ICommandRoute;

    public function handle($argv): void;
}