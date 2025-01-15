<?php

use Ppm\Framework\CurrentAssembly;

const PPM_FRAMEWORK_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'Ppm.Framework.phar';
const ENTRYPOINT = ['ENTRYPOINT_CLASS', 'ENTRYPOINT_METHOD'];
define('ARGV', $argv);

include PPM_FRAMEWORK_PATH;

CurrentAssembly::getAssembly()
    ->includeProjectLibrary('PROJECT_NAME')
    ->entrypoint(
        ENTRYPOINT,
        $argv ?? []
    );


