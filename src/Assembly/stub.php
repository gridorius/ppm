<?php
Phar::mapPhar('Assembly');
$manifest = include "phar://Assembly/manifest.php";
spl_autoload_register(function ($entity) use ($manifest) {
    if (key_exists($entity, $manifest['types']))
        include $manifest['types'][$entity];
});
__HALT_COMPILER();