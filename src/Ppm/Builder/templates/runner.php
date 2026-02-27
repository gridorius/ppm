<?php


use Ppm\Framework\Application;

const PPM_FRAMEWORK_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'Ppm.Framework.phar';
const ENTRYPOINT = 'ENTRYPOINT_DATA';
define('ARGV', $argv);

include PPM_FRAMEWORK_PATH;

Application::includeProjectLibrary('PROJECT_NAME');
Application::entrypoint(
    ENTRYPOINT,
    $argv ?? []
);


