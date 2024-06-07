<?php

namespace Packages;

use Packages\Contracts\ISource;
use Packages\Contracts\ISources;
use Packages\Http\Client;
use Packages\Http\Response;
use Utils\PathUtils;

class Sources implements ISources
{
    private string $sourcesFilePath;
    private array $sources;
    private int $index = 0;

    private array $keys;

    public function __construct(string $sourcesFilePath)
    {
        $this->sourcesFilePath = $sourcesFilePath;
        $directory = dirname($sourcesFilePath);
        if (!is_dir($directory))
            mkdir($directory, 0755, true);
        if (!is_file($sourcesFilePath))
            file_put_contents($this->sourcesFilePath, json_encode([]));

        $this->sources = [];
        foreach (PathUtils::getJson($this->sourcesFilePath) as $source => $data)
            $this->sources[$source] = new Source($data['path'], $data['token']);

        $this->updateKeys();
    }

    public function add(ISource $source): void
    {
        $this->sources[$source->getPath()] = $source;
        $this->updateFile();
    }

    public function delete(ISource $source): void
    {
        unset($this->sources[$source->getPath()]);
        $this->updateFile();
    }

    public function get(string $source): ?ISource
    {
        return $this->sources[$source];
    }

    public function has(string $source): bool
    {
        return !empty($this->sources[$source]);
    }

    public function current(): ISource
    {
        return $this->sources[$this->keys[$this->index]];
    }

    public function key(): string
    {
        return $this->keys[$this->index];
    }

    public function next()
    {
        ++$this->index;
    }

    public function valid()
    {
        return isset($this->keys[$this->index]);
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function authorize(string $source, string $login, string $password): void
    {
        $client = new Client();
        $source = $this->has($source) ? $this->get($source) : new Source($source);
        $response = $client
            ->post($source->makeRequestPath('/download'))
            ->body([
                'login' => $login,
                'password' => $password
            ])
            ->execute();

        $response
            ->awaitCode(200, function (Response $response) use ($source) {
                $source->setToken($response['content']);
                $this->add($source);
            })
            ->awaitCode(400, function () {
                throw new \Exception('Authorization failed');
            });
    }

    private function updateKeys(): void
    {
        $this->keys = array_keys($this->sources);
    }

    private function updateFile()
    {
        file_put_contents(
            $this->sourcesFilePath,
            json_encode($this->sources, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }
}