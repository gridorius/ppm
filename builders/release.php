<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
$directory = realpath(__DIR__ . '/../') . '/ppm';
buildPPM($directory);
$tar = new PharData(__DIR__ . '/../ppm.tar');
$tar->startBuffering();
$tar->buildFromDirectory($directory);
$tar->stopBuffering();
\Ppm\Framework\Filesystem\Directory::from($directory)->delete();
