<?php

use Ppm\Framework\CurrentAssembly;

const RUNNER_PATH = __FILE__;
const PPM_FRAMEWORK_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'Ppm.Framework.phar';
const ENTRYPOINT_PROJECT_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'PROJECT_NAME.phar';
const ENTRYPOINT = ['ENTRYPOINT_CLASS', 'ENTRYPOINT_METHOD'];

include PPM_FRAMEWORK_PATH;
include ENTRYPOINT_PROJECT_PATH;

CurrentAssembly::getAssembly()->entrypoint(
    ENTRYPOINT,
    $argv ?? []
);


