<?php

use Builder\BuildManager;
use Utils\ReplaceUtils;

include __DIR__.DIRECTORY_SEPARATOR.'includes.php';
const WIN = false;
// build ppm
$director = new BuildManager(realpath(__DIR__ . '/../src/PPM/PPM.proj.json'));
$director->build('/usr/lib/ppm');

// add ppm to executable commands
$commandPath = '/usr/bin/ppm';
$command = file_get_contents(__DIR__.'/assets/ppm');
file_put_contents($commandPath, ReplaceUtils::prepareLF($command));
chmod($commandPath, 0755);