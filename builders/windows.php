<?php

use Builder\BuildManager;
use Utils\ReplaceUtils;

include __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

const WIN = true;
$outPath = 'C:\Program Files\ppm';
$ppmPaths = [PPM_BUILD_DIRECTORY, $outPath];
$assemblyPaths = [ASSEMBLY_BUILD_DIRECTORY, $outPath];
$buildManager = new BuildManager();
$buildManager->build(...$ppmPaths);
$buildManager->build(...$assemblyPaths);

$commandPath = 'C:\Windows\ppm.bat';
$command = file_get_contents(__DIR__ . '/assets/ppm.bat');
file_put_contents($commandPath, ReplaceUtils::prepareLF($command));
chmod($commandPath, 0755);