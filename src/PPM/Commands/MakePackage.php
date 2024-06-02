<?php

namespace PPM\Commands;

use PPM\Commands\Contracts\CommandBase;
use Packages\PackageManager;
use Terminal\OptionParser;
use Utils\PathUtils;

class MakePackage extends CommandBase
{
    public function execute(array $argv)
    {
        $buildDir = $argv[0] ?? getcwd();
        array_shift($argv);
        $options = OptionParser::parse($argv, [
            'p' => true
        ]);
        $manager = new PackageManager();
        $packagePath = $manager->getBuilder()->buildPackage(PathUtils::resolveRelativePath(getcwd(), $buildDir));
        if(!empty($options['p']))
            $manager->getRemote()->uploadPackage($packagePath, $options['p']);
    }
}