<?php

use Ppm\Builder\BuildManager;

spl_autoload_register(function ($entity) {
    $entityPath = preg_replace("/\\\/", '/', $entity) . '.php';
    include __DIR__ . '/../src/' . $entityPath;
});

define("PPM_BUILD_DIRECTORY", realpath(__DIR__ . '/../src/Ppm/Core/PpmCore.proj.json'));
define("ASSEMBLY_BUILD_DIRECTORY", realpath(__DIR__ . '/../src/Ppm/Framework/PpmFramework.proj.json'));

function buildPPM(string $outPath): void
{
    $corePaths = [PPM_BUILD_DIRECTORY, $outPath];
    $frameworkPaths = [ASSEMBLY_BUILD_DIRECTORY, $outPath];
    $buildManager = new BuildManager();
    $buildManager->build(...$corePaths);
    $buildManager->build(...$frameworkPaths);
}