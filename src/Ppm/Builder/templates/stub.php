<?php

use Ppm\Framework\Application;

Phar::mapPhar('PROJECT_NAME');
Application::registerAssembly('PROJECT_NAME', __DIR__);
__HALT_COMPILER();