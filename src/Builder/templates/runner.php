<?php

use Assembly\Assembly;

include __DIR__ . DIRECTORY_SEPARATOR . 'Assembly.phar';
include __DIR__ . DIRECTORY_SEPARATOR . 'PROJECT_NAME.phar';

Assembly::getInstance()->entrypoint(
    ['ENTRYPOINT_CLASS', 'ENTRYPOINT_METHOD'],
    $argv ?? []
);


