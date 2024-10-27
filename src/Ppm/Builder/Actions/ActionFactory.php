<?php

namespace Ppm\Builder\Actions;

use Exception;

class ActionFactory
{
    public static function createAction(array $arguments): IAction
    {
        switch ($arguments['type']) {
            case 'delete':
                return new DeleteAction($arguments['template']);
            case 'shell':
                return new ShellAction($arguments['command']);
            default:
                throw new Exception("Unexpected action type {$arguments['type']}");
        }
    }
}