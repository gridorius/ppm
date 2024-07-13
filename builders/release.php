<?php

use Builder\BuildManager;

include __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
const WIN = false;
// build ppm
$outPath = realpath(__DIR__ . '/../ppm');
$ppmPaths = [PPM_BUILD_DIRECTORY, $outPath];
$assemblyPaths = [ASSEMBLY_BUILD_DIRECTORY, $outPath];
$buildManager = new BuildManager();
$buildManager->build(...$ppmPaths);
$buildManager->build(...$assemblyPaths);
