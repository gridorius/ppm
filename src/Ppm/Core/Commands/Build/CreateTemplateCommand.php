<?php

namespace Ppm\Core\Commands\Build;

use ArrayIterator;
use PharData;
use Ppm\Core\Solution;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\Path;
use Ppm\Framework\Terminal\CommandRouting\Contracts\CommandBase;

class CreateTemplateCommand extends CommandBase
{
    public function execute(array $parameters, array $options, array $argv): void
    {
        $solution = Solution::getSolutionOrThrow();
        $solutionFiles = Directory::getDirectoryFiles($solution->getDirectory());
        $templateName = $parameters['name'];
        Directory::createDirectory(Path::assemblyCombine('templates'));
        $pharName = Path::assemblyCombine('templates', $templateName . '.tar');
        $files = [];
        foreach ($solutionFiles as $localPath => $absolutePath)
            if (!preg_match("/^([Bb]in|\.idea|\.git|\.ppm|\.github|\.gitignore)/", $localPath))
                $files[$localPath] = $absolutePath;
        $phar = new PharData($pharName);
        $phar->startBuffering();
        $phar->buildFromIterator(new ArrayIterator($files));
        $phar->stopBuffering();
    }
}