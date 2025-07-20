<?php

namespace Ppm\Builder\Actions;

class ShellAction extends ActionBase
{
    public function run(): void
    {
        $command = $this->prepareString($this->arguments['command']);
        echo "Running shell command: {$command}\n";
        $resource = proc_open($command, [
            1 => STDOUT,
            2 => STDERR,
        ], $pipes);;

        if ($this->arguments['wait'] ?? true) {
            while (proc_get_status($resource)['running'])
                sleep(1);
            if (proc_get_status($resource)['exitcode'] !== 0)
                throw new \Exception("Shell command failed");
        }
    }
}