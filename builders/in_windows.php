<?php

use Builder\BuildManager;
use Utils\ReplaceUtils;

include __DIR__.DIRECTORY_SEPARATOR.'includes.php';

const WIN = true;
$director = new BuildManager(realpath(__DIR__ . '/../src/PPM/PPM.proj.json'));
$director->build('C:\Program Files\ppm');

$commandPath = 'C:\Windows\ppm.bat';
$command = file_get_contents(__DIR__.'/assets/ppm.bat');
file_put_contents($commandPath, ReplaceUtils::prepareLF($command));
chmod($commandPath, 0755);