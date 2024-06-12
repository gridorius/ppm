<?php

spl_autoload_register(function ($entity) {
    $entityPairs = explode('\\', $entity);
    $entityPath = implode('/', $entityPairs) . '.php';
    include __DIR__ . '/../src/' . $entityPath;
});

define("PPM_BUILD_DIRECTORY", realpath(__DIR__ . '/../src/PPM/PPM.proj.json'));
define("ASSEMBLY_BUILD_DIRECTORY", realpath(__DIR__ . '/../src/Assembly/Assembly.proj.json'));