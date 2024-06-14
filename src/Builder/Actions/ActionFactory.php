<?php

namespace Builder\Actions;

class ActionFactory
{
    public static function createAction(array $arguments): IAction
    {
        switch ($arguments['type']) {
            case 'copy':
                return new CopyAction($arguments['from'], $arguments['to']);
            case 'delete':
                return new DeleteAction($arguments['file'] ?? null, $arguments['directory'] ?? null);
            case 'shell':
                return new ShellAction($arguments['command']);
        }
    }
}