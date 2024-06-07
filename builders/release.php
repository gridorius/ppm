<?php

use Builder\BuildManager;

include __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';
const WIN = false;
// build ppm
$outPath = __DIR__ . '/../';
$ppmPaths = [realpath(__DIR__ . '/../src/PPM/PPM.proj.json'), $outPath];
$assemblyPaths = [realpath(__DIR__ . '/../src/Assembly/Assembly.proj.json'), $outPath];
$director = new BuildManager();
$director->build(...$ppmPaths);
$director->build(...$assemblyPaths);
