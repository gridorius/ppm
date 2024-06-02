<?php

use Builder\BuildManager;

include __DIR__.DIRECTORY_SEPARATOR.'includes.php';
const WIN = false;
// build ppm
$director = new BuildManager(realpath(__DIR__ . '/../src/PPM/PPM.proj.json'));
$director->build(realpath(__DIR__.'/../').DIRECTORY_SEPARATOR.'ppm');
