<?php

namespace Packages;

use Exception;
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

    public function add(ISource $source, ?string $alias = null): void
    {
        $this->sources[$alias ?? $source->getPath()] = $source;
        $this->updateFile();
    }

    public function delete(string $source): void
    {
        unset($this->sources[$source]);
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

    public function next(): void
    {
        ++$this->index;
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->index]);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function authorize(string $source, string $login, string $password, ?string $alias = null): void
    {
        $client = new Client();
        $source = $this->has($source) ? $this->get($source) : $this->createSource($source, $alias);
        $response = $client
            ->post($source->makeRequestPath('auth'))
            ->disableSsl()
            ->headers([
                'Content-Type' => 'multipart/form-data',
                'Client' => 'ppm client'
            ])
            ->body([
                'login' => $login,
                'password' => $password
            ])
            ->execute();

        $response
            ->awaitCode(200, function (Response $response) use ($source) {
                $source->setToken($response->json()['token']);
                $this->updateFile();
                echo "Successful authorization\n";
            })
            ->awaitCode(400, function () {
                throw new Exception('Authorization failed');
            });
    }

    private function updateKeys(): void
    {
        $this->keys = array_keys($this->sources);
    }

    private function updateFile(): void
    {
        file_put_contents(
            $this->sourcesFilePath,
            json_encode($this->sources, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    public function count(): int
    {
        return count($this->sources);
    }

    public function createSource(string $path, ?string $alias = null): ISource
    {
        return $this->sources[$alias ?? $path] = new Source($path);
    }
}