<?php

namespace Ppm\Core\Commands\Architecture;

use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class InitializeCommand extends CommandBase
{

    public function getDescription(): string
    {
        return "Create solution in current directory";
    }

    public function execute(array $parameters, array $options, array $argv): void
    {
        $name = $parameters['name'];
        $directory = getcwd() . DIRECTORY_SEPARATOR . $name;
        mkdir($directory, 0755, true);
        file_put_contents($directory . '/.gitignore',
            <<<GITIGNORE
            .ppm
            GITIGNORE
        );
        file_put_contents($directory . '/solution.json', json_encode([
            'projects' => []
        ]));
    }
}