<?php

use Assembly\Assembly;

Phar::mapPhar('PROJECT_NAME');
Assembly::registerAssembly('PROJECT_NAME', __DIR__);
__HALT_COMPILER();