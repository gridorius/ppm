<?php

namespace Packages;

use Exception;
use Packages\Http\Client;
use Packages\Http\Response;
use Utils\PathUtils;

class RemotePackages
{
    protected string $authPath;
    protected string $sourcesPath;

    public function __construct()
    {
        $configurationPath = \Assembly::path('configuration');
        $this->authPath = \Assembly::path('configuration', 'auth.json');
        $this->sourcesPath = \Assembly::path('configuration', 'sources.json');

        if (!is_dir($configurationPath))
            mkdir($configurationPath, 0755, true);

        if (!file_exists($this->authPath))
            $this->saveAuthData([]);
        if (!file_exists($this->sourcesPath))
            $this->saveSources([]);
    }

    public function auth(string $source, string $login, string $password)
    {
        $client = new Client();
        $response = $client
            ->post($source . '/auth')
            ->body([
                'login' => $login,
                'password' => $password
            ])
            ->execute();

        $response
            ->awaitCode(200, function (Response $response) use ($source) {
                $authData = $this->getAuth();
                $authData[$source] = $response->json()['token'];
                $this->saveAuthData($authData);
            })
            ->awaitCode(400, $this->getErrorResponseCallback());
    }

    public function addSource(string $source)
    {
        $sources = $this->getSources();
        $sources[] = $source;
        $this->saveSources($sources);
    }

    public function removeSource(string $source)
    {
        $sources = $this->getSources();
        $sources = array_filter($sources, function ($s) use ($source) {
            return $s != $source;
        });
        $this->saveSources($sources);
    }

    public function uploadPackage(string $packagePath, string $source)
    {
        $client = new Client();
        $authorizationData = $this->getAuth();
        $response = $client
            ->post($source . '/catalog/upload')
            ->body([
                'package' => curl_file_create($packagePath)
            ])
            ->headers([
                'Authorization' => $authorizationData[$source]
            ])
            ->execute();

        $response
            ->awaitCode(200, function (Response $response) {
                echo "Successful upload package\n";
            })
            ->awaitCodes([400, 401, 403], $this->getErrorResponseCallback());
    }

    public function findPackage(string $name, string $version): ?array
    {
        $sources = $this->getSources();
        $client = new Client();
        $authorizationData = $this->getAuth();
        $foundPackage = null;
        foreach ($sources as $source) {
            $response = $client
                ->get($source . '/catalog/find')
                ->query([
                    'package' => $name,
                    'version' => $version
                ])
                ->headers([
                    'Authorization' => $authorizationData[$source]
                ])
                ->execute();

            $response
                ->awaitCode(200, function (Response $response) use (&$foundPackage) {
                    $foundPackage = $response->json();
                })
                ->awaitCodes([400, 401, 403, 404], $this->getErrorResponseCallback());
        }

        return $foundPackage;
    }

    public function downloadPackage(string $name, string $version, string $source, string $saveDirectory): string
    {
        $client = new Client();
        $authorizationData = $this->getAuth();
        $client->setProgressFunction(function ($percent) use ($name, $version) {
            echo "Download package {$name}:{$version} - {$percent}%\r";
        });
        echo "\n";
        $response = $client->get($source . '/download',
            [
                'package' => $name,
                'version' => $version
            ],
            [
                'Authorization' => $authorizationData[$source]
            ]
        );

        if ($response['headers']['code'] == 200) {
            $localPath = $saveDirectory . DIRECTORY_SEPARATOR . PathUtils::getPackageName($name, $version);
            file_put_contents($localPath, $response['content']);
            return $localPath;
        } else {
            throw new Exception($response['content']);
        }
    }

    public function getSources(): array
    {
        return PathUtils::getJson($this->sourcesPath);
    }

    public function getAuth(): array
    {
        return PathUtils::getJson($this->authPath);
    }

    protected function saveSources(array $data): void
    {
        file_put_contents($this->sourcesPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    protected function saveAuthData(array $data): void
    {
        file_put_contents($this->authPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    protected function getErrorResponseCallback(): \Closure
    {
        return function (Response $response) {
            throw new Exception($response->json()['error']);
        };
    }
}