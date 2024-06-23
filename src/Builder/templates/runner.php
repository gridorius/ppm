<?php

use Assembly\Assembly;

include __DIR__ . DIRECTORY_SEPARATOR . 'Assembly.phar';
include __DIR__ . DIRECTORY_SEPARATOR . 'PROJECT_NAME.phar';

Assembly::entrypoint(
    ['ENTRYPOINT_CLASS', 'ENTRYPOINT_METHOD'],
    $argv ?? []
);


