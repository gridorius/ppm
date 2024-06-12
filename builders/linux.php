<?php

use Builder\BuildManager;
use Utils\ReplaceUtils;

include __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
const WIN = false;
// build ppm
$outPath = '/usr/lib/ppm';
$ppmPaths = [PPM_BUILD_DIRECTORY, $outPath];
$assemblyPaths = [ASSEMBLY_BUILD_DIRECTORY, $outPath];
$buildManager = new BuildManager();
$buildManager->build(...$ppmPaths);
$buildManager->build(...$assemblyPaths);

// add ppm to executable commands
$commandPath = '/usr/bin/ppm';
$command = file_get_contents(__DIR__ . '/assets/ppm');
file_put_contents($commandPath, ReplaceUtils::prepareLF($command));
chmod($commandPath, 0755);