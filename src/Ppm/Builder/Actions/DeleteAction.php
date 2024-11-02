<?php

namespace Ppm\Builder\Actions;

use Exception;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\File;
use Ppm\Framework\Filesystem\PathUtils;
use Ppm\Framework\Utils\StringUtils;

class DeleteAction extends ActionBase
{
    private string $template;

    public function __construct(string $template)
    {
        $this->template = $template;
    }

    public function run(): void
    {
        $template = $this->prepareString($this->template);
        $position = strpos($template, '*');
        if ($position !== false) {
            $directory = Directory::from(substr($template, 0, $position));
            $filesTemplate = substr($template, $position + 1);
            foreach ($directory->getFiles() as $file)
                if (fnmatch($filesTemplate, $file))
                    unlink($file);
        } elseif (is_dir($template)) {
            Directory::from($template)->clear();
        } else if (is_file($template)) {
            File::from($template)->delete();
        }
    }

    private function log(string $path): void
    {
        echo "Deleted {$path}\n";
    }
}