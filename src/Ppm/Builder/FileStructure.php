<?php

namespace Ppm\Builder;

use Ppm\Framework\Filesystem\PathUtils;

class FileStructure
{
    private array $structure = [];

    /**
     * scan project directory files
     *
     * @param string $path
     * @return void
     */
    public function scanDirectory(string $path): void
    {
        $files = PathUtils::scanDirectory($path);
        $projects = $this->splitProjects($files);
        foreach ($projects as $projectDirectory => $files)
            $this->structure[$projectDirectory] = $files;
    }

    /**
     * return files from project directory
     *
     * @param string $projectDirectory
     * @return array
     */
    public function getProjectFiles(string $projectDirectory): array
    {
        return $this->structure[$projectDirectory];
    }

    public function hasProject(string $projectDirectory): bool
    {
        return key_exists($projectDirectory, $this->structure);
    }

    /**
     * split directory projects
     *
     * @param $items
     * @return array
     */
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
                    $relativePath = preg_replace("/\\\\/", "/", substr($item, $offset));
                    $projectsFiles[$projectDirectory][$item] = $relativePath;
                    $itemList[$item] = true;
                }
            }
        }

        return $projectsFiles;
    }
}