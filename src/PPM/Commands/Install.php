<?php

namespace PPM\Commands;

use Assembly\Resources;
use Assembly\Utils;
use Terminal\CommandRouting\CommandBase;

class Install extends CommandBase
{
    public function execute(array $parameters, array $options): void
    {
        $pathToPPM = Utils::path('ppm.php');
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
        $this->handleResult($result, $path);
    }

    private function handleResult($result, string $path): void
    {
        if ($result === false) {
            echo "Install failed\n";
            exit(1);
        }
        echo "Installed to {$path}\n";
    }
}