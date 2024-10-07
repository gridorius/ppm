<?php

use Ppm\Framework\Assembly;

include __DIR__ . DIRECTORY_SEPARATOR . 'Ppm.Framework.phar';
include __DIR__ . DIRECTORY_SEPARATOR . 'PROJECT_NAME.phar';

Assembly::entrypoint(
    ['ENTRYPOINT_CLASS', 'ENTRYPOINT_METHOD'],
    $argv ?? []
);


