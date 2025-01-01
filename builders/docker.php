<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
buildPPM('/usr/lib/ppm');
shell_exec('php /usr/lib/ppm/ppm.php install');
