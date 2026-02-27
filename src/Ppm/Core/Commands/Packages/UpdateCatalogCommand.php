<?php

namespace Ppm\Core\Commands\Packages;

use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;
use Ppm\Packages\PackagesManager;

class UpdateCatalogCommand extends CommandBase
{
    public function execute(array $parameters, array $options, array $argv): void
    {
        $manager = new PackagesManager();
        $manager->getRemoteManager()->updateCatalogs();
    }
}