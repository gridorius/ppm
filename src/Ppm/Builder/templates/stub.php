<?php

use Ppm\Framework\CurrentAssembly;

Phar::mapPhar('PROJECT_NAME');
CurrentAssembly::getAssembly()->registerAssembly('PROJECT_NAME', __DIR__);
__HALT_COMPILER();