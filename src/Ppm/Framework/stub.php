<?php


Phar::mapPhar('Ppm.Framework');
$manifest = include "phar://Ppm.Framework/manifest.php";
spl_autoload_register(function ($entity) use ($manifest) {
    if (key_exists($entity, $manifest['types']))
        include $manifest['types'][$entity];
});
__HALT_COMPILER();