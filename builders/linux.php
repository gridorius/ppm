<?php

use Builder\BuildManager;
use Utils\ReplaceUtils;

include __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';
const WIN = false;
// build ppm
$outPath = '/usr/lib/ppm';
$ppmPaths = [realpath(__DIR__ . '/../src/PPM/PPM.proj.json'), $outPath];
$assemblyPaths = [realpath(__DIR__ . '/../src/Assembly/Assembly.proj.json'), $outPath];
$director = new BuildManager();
$director->build(...$ppmPaths);
$director->build(...$assemblyPaths);

// add ppm to executable commands
$commandPath = '/usr/bin/ppm';
$command = file_get_contents(__DIR__ . '/assets/ppm');
file_put_contents($commandPath, ReplaceUtils::prepareLF($command));
chmod($commandPath, 0755);