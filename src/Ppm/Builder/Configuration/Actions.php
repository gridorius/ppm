<?php

namespace Ppm\Builder\Configuration;

use Ppm\Builder\Actions\ActionFactory;
use Ppm\Builder\Actions\IAction;

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

    /**
     * run before build actions
     *
     * @param string $buildDirectory
     * @param string $outDirectory
     * @return void
     */
    public function runBeforeBuild(string $buildDirectory, string $outDirectory): void
    {
        foreach ($this->beforeBuild as $action) {
            $action->setDirectories($buildDirectory, $outDirectory);
            $action->run();
        }
    }

    /**
     * run after build actions
     *
     * @param string $buildDirectory
     * @param string $outDirectory
     * @return void
     */
    public function runAfterBuild(string $buildDirectory, string $outDirectory): void
    {
        foreach ($this->afterBuild as $action) {
            $action->setDirectories($buildDirectory, $outDirectory);
            $action->run();
        }
    }
}