<?php

namespace Ppm\Core\Commands;

use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Resources\Resources;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class InstallCommand extends CommandBase
{
    public function getDescription(): string
    {
        return 'Create link on this project in executable directory';
    }

    public function execute(array $parameters, array $options): void
    {
        $pathToPPM = Path::assemblyCombine('ppm.php');
        if (WIN) {
            $path = 'C:\Windows\ppm.bat';
            $result = file_put_contents(
                $path,
                preg_replace("/PPM_PATH/", $pathToPPM, Resources::get('resources/ppm.bat')->getContent())
            );
        } else {
            $path = '/usr/bin/ppm';
            $result = file_put_contents(
                $path,
                preg_replace("/PPM_PATH/", $pathToPPM, Resources::get('resources/ppm.sh')->getContent())
            );
        }
        $this->checkResult($result, $path);
    }

    private function checkResult($result, string $path): void
    {
        if ($result === false) {
            echo "Install failed\n";
            exit(1);
        }
        echo "Installed to {$path}\n";
    }
}