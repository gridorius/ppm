<?php

namespace Ppm\Core\Commands\Architecture;

use Ppm\Core\Solution;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class CreateProjectCommand extends CommandBase
{
    public function getDescription(): string
    {
        return "Create project directory in this solution";
    }

    public function execute(array $parameters, array $options, array $argv): void
    {
        $solution = Solution::getSolutionOrThrow();
        $name = $parameters['name'];
        $directory = getcwd() . DIRECTORY_SEPARATOR . $name;
        mkdir($directory, 0755, true);
        file_put_contents($directory . DIRECTORY_SEPARATOR . "{$name}.proj.json", json_encode([
            'name' => $name,
            'version' => 'latest',
            'author' => '',
            'description' => '',
            'projects' => [],
            'packages' => [],
            'resources' => [],
            'includes' => [],
        ], JSON_PRETTY_PRINT));
        $data = $solution->getData();
        $data['projects'][$name] = substr($directory, strlen($solution->getDirectory()) + 1);
        $solution->save($data);
    }
}