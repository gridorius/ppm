<?php
namespace Builder;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Scanner
{
    public static function scanDirectory(string $path): array
    {
        $dirIterator = new RecursiveDirectoryIterator($path,
            FilesystemIterator::CURRENT_AS_PATHNAME
            | FilesystemIterator::KEY_AS_PATHNAME
            | FilesystemIterator::SKIP_DOTS
        );
        $iterator = new RecursiveIteratorIterator($dirIterator);
        $files = [];
        foreach ($iterator as $path){
            $files[] = $path;
        }

        return static::splitProjects($files);
    }

    protected static function splitProjects($items): array{
        $projectDirectories = [];
        $itemList = [];
        foreach($items as $path){
            if(fnmatch('*.proj.json', $path))
                $projectDirectories[] = pathinfo($path, PATHINFO_DIRNAME);
            $itemList[$path] = false;
        }


        usort($projectDirectories, function($a, $b){
            $al = strlen($a);
            $bl = strlen($b);

            return $al == $bl ? 0 : ($al > $bl ? -1 : 1 );
        });

        $projectsFiles = [];
        foreach($projectDirectories as $projectDirectory){
            $projectsFiles[$projectDirectory] = [];
        }

        foreach($projectDirectories as $projectDirectory){
            foreach($items as $item){
                if(fnmatch($projectDirectory.DIRECTORY_SEPARATOR.'*', $item, FNM_NOESCAPE) && !$itemList[$item]){
                    $projectsFiles[$projectDirectory][] = $item;
                    $itemList[$item] = true;
                }
            }
        }

        return $projectsFiles;
    }
}