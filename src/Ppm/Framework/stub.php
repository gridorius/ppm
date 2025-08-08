<?php


use Ppm\Framework\Assembly;

Phar::mapPhar('Ppm.Framework');
$manifest = include "phar://Ppm.Framework/manifest.php";
spl_autoload_register(function ($entity) use ($manifest) {
    if (key_exists($entity, $manifest['types']))
        include $manifest['types'][$entity];
});
$assembly = new Assembly('Ppm.Framework', __DIR__);
$assembly->registerResources();
$assembly->includeScripts();
__HALT_COMPILER();