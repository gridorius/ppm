<?php

spl_autoload_register(function ($entity){
   $entityPairs = explode('\\', $entity);
   $entityPath = implode('/', $entityPairs).'.php';
   include __DIR__.'/../src/'.$entityPath;
});