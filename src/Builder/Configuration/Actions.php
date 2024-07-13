<?php

namespace Builder\Configuration;

use Builder\Actions\ActionFactory;
use Builder\Actions\IAction;

class Actions
{
    /**
     * @var IAction[]
     */
    private array $beforeBuild;

    /**
     * @var IAction[]
     */
    private array $afterBuild;

    public function __construct(array $actions)
    {
        $this->beforeBuild = [];
        $this->afterBuild = [];

        foreach ($actions as $action) {
            if (empty($action['on']))
                $action['on'] = 'AfterBuild';

            switch ($action['on']) {
                case 'BeforeBuild':
                    $this->beforeBuild[] = ActionFactory::createAction($action);
                    break;
                case 'AfterBuild':
                    $this->afterBuild[] = ActionFactory::createAction($action);
                    break;
            }
        }
    }

    public function runBeforeBuild(string $buildDirectory, string $outDirectory): void
    {
        foreach ($this->beforeBuild as $action) {
            $action->setDirectories($buildDirectory, $outDirectory);
            $action->run();
        }
    }

    public function runAfterBuild(string $buildDirectory, string $outDirectory): void
    {
        foreach ($this->afterBuild as $action) {
            $action->setDirectories($buildDirectory, $outDirectory);
            $action->run();
        }
    }
}