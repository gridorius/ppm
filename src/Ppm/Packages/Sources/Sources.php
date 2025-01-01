<?php

namespace Ppm\Packages\Sources;

use Countable;
use Exception;
use Iterator;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\PathUtils;
use Ppm\Framework\Network\Client\Body\FormUrlencodedBody;
use Ppm\Framework\Network\Client\HttpRequestHelper;
use Ppm\Framework\Network\Client\HttpResponse;

class Sources implements Iterator, Countable
{
    private string $sourcesFilePath;
    private array $sources;
    private int $index = 0;
    private array $keys;

    public function __construct(string $sourcesFilePath)
    {
        $this->sourcesFilePath = $sourcesFilePath;
        $directory = dirname($sourcesFilePath);
        Directory::createDirectory($directory);
        if (!is_file($sourcesFilePath))
            file_put_contents($this->sourcesFilePath, json_encode([]));

        $this->sources = [];
        foreach (PathUtils::parseJson($this->sourcesFilePath) as $source => $data)
            $this->sources[$source] = new Source($data['path'], $data['id'], $data['token']);

        $this->updateKeys();
    }

    public function add(Source $source, ?string $alias = null): void
    {
        $this->sources[$alias ?? $source->getId()] = $source;
        $this->updateFile();
    }

    public function delete(string $source): void
    {
        unset($this->sources[$source]);
        $this->updateFile();
    }

    public function get(string $source): ?Source
    {
        return $this->sources[$source];
    }

    public function getById(string $id): ?Source
    {
        return array_filter($this->sources, function (Source $source) use ($id) {
            return $source->getId() === $id;
        })[0] ?? null;
    }

    public function toIdsArray(): array
    {
        $result = [];
        foreach ($this->sources as $source)
            $result[$source->getId()] = $source;
        return $result;
    }

    public function has(string $source): bool
    {
        return !empty($this->sources[$source]);
    }

    public function current(): Source
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
        $client = new HttpRequestHelper();
        $source = $this->has($source) ? $this->get($source) : $this->createSource($source, $alias);
        $response = HttpRequestHelper::post($source->makeRequestPath('auth'))
            ->setHeaders([
                'Client: ppm client'
            ])
            ->setBody(new FormUrlencodedBody([
                'login' => $login,
                'password' => $password
            ]))
            ->send()
            ->waitResponse();

        $response
            ->awaitCode(200, function (HttpResponse $response) use ($source) {
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

    public function createSource(string $path, ?string $alias = null): Source
    {
        return $this->sources[$alias ?? $path] = new Source($path);
    }
}