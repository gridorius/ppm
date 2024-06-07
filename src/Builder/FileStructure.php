<?php

namespace Builder;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileStructure
{
    protected array $structure = [];

    public function scanDirectory(string $path): void
    {
        $files = $this->getDirectoryFiles($path);
        $projects = $this->splitProjects($files);
        foreach ($projects as $projectDirectory => $files)
            $this->structure[$projectDirectory] = $files;
    }

    public function getProjectFiles(string $projectDirectory): array
    {
        return $this->structure[$projectDirectory];
    }

    public function hasProject(string $projectDirectory): bool
    {
        return key_exists($projectDirectory, $this->structure);
    }

    protected function getDirectoryFiles(string $path): array
    {
        $dirIterator = new RecursiveDirectoryIterator($path,
            FilesystemIterator::CURRENT_AS_PATHNAME
            | FilesystemIterator::KEY_AS_PATHNAME
            | FilesystemIterator::SKIP_DOTS
        );
        $iterator = new RecursiveIteratorIterator($dirIterator);
        $files = [];
        foreach ($iterator as $path) {
            $files[] = $path;
        }
        return $files;
    }

    protected function splitProjects($items): array
    {
        $projectDirectories = [];
        $itemList = [];
        foreach ($items as $path) {
            if (fnmatch('*proj.json', $path))
                $projectDirectories[] = pathinfo($path, PATHINFO_DIRNAME);
            $itemList[$path] = false;
        }


        usort($projectDirectories, function ($a, $b) {
            $al = strlen($a);
            $bl = strlen($b);

            return $al == $bl ? 0 : ($al > $bl ? -1 : 1);
        });

        $projectsFiles = [];
        foreach ($projectDirectories as $projectDirectory) {
            $projectsFiles[$projectDirectory] = [];
        }

        foreach ($projectDirectories as $projectDirectory) {
            $offset = strlen($projectDirectory) + 1;
            foreach ($items as $item) {
                if (fnmatch($projectDirectory . DIRECTORY_SEPARATOR . '*', $item, FNM_NOESCAPE) && !$itemList[$item]) {
                    $projectsFiles[$projectDirectory][$item] = substr($item, $offset);
                    $itemList[$item] = true;
                }
            }
        }

        return $projectsFiles;
    }
}