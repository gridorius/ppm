<?php

use Builder\BuildManager;
use Utils\ReplaceUtils;

include __DIR__.DIRECTORY_SEPARATOR.'includes.php';

const WIN = false;
$director = new BuildManager(realpath(__DIR__ . '/../src/PPM/PPM.proj.json'));
$director->build('/ppm/usr/lib/ppm');
$conf = $director->getScanner()->getConfiguration();

$commandPath = '/ppm/usr/bin/ppm';
$command = file_get_contents(__DIR__.'/assets/ppm');
file_put_contents($commandPath, ReplaceUtils::prepareLF($command));
chmod($commandPath, 0755);

$directories = [
    '/usr/bin/ppm',
    '/usr/lib/ppm',
];
$directoriesString = implode("\n", $directories);
foreach ($directories as $directory)
    mkdir($directory, 0755, true);
mkdir('/ppm/DEBIAN', 0755, true);

$control = ReplaceUtils::replace(['NAME', 'VERSION'], [$conf['name'], $conf['version']], file_get_contents(__DIR__.'/assets/control'));
file_put_contents('/ppm/DEBIAN/control', ReplaceUtils::prepareLF($control));
file_put_contents('/ppm/DEBIAN/dirs', $directoriesString);
