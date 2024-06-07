<?php

use Assembly\Assembly;

Phar::mapPhar('PROJECT_NAME');
Assembly::getInstance()->registerAssembly('PROJECT_NAME', __DIR__);
__HALT_COMPILER();