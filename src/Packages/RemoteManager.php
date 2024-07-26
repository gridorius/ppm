<?php

namespace Packages;

use Builder\Configuration\Contracts\IConfigurationCollection;
use Closure;
use Packages\Contracts\ILocalManager;
use Packages\Contracts\ILocalPackage;
use Packages\Contracts\IRemoteManager;
use Packages\Contracts\IRemotePackage;
use Packages\Contracts\ISource;
use Packages\Contracts\ISources;
use Packages\Exceptions\BadRequestException;
use Packages\Exceptions\PackageNotFoundException;
use Packages\Http\Client;
use Packages\Http\Response;
use Utils\PathUtils;

class RemoteManager implements IRemoteManager
{
    private ISources $sources;
    private Client $client;
    private ILocalManager $localManager;
    private string $tmpDirectory;

    public function __construct(ISources $sources, ILocalManager $localManager, string $tmpDirectory)
    {
        $this->sources = $sources;
        $this->localManager = $localManager;
        $this->client = new Client();
        $this->tmpDirectory = PathUtils::createDirectory($tmpDirectory);
    }

    public function find(string $name, string $version): ?IRemotePackage
    {
        $foundPackage = null;
        foreach ($this->sources as $source) {
            try {
                $response = $this
                    ->client
                    ->get($source->makeRequestPath("catalog/{$name}/{$version}/info/"))
                    ->disableSsl()
                    ->headers($source->makeAuthHeaders())
                    ->execute();

                $response
                    ->awaitCode(200, function (Response $response) use (&$foundPackage, $source) {
                        $data = $response->json();
                        $foundPackage = new RemotePackage($data['name'], $data['version'], $source, $data['depends']);
                        echo "Package {$data['name']}:{$data['version']} found in {$source->getPath()}" . PHP_EOL;
                    })
                    ->awaitCodes([400, 401, 403, 404], $this->getErrorResponseCallback());
            } catch (\Exception $exception) {
                echo $exception->getMessage() . PHP_EOL;
            }
        }

        return $foundPackage;
    }

    public function upload(ILocalPackage $localPackage, ISource $source): void
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $localPath = $this->compactLocalPackage($localPackage);
        echo "Package compacted start uploading" . PHP_EOL;
        $size = filesize($localPath);
        $this->client->setProgressFunction(function ($headers, $downloaded, $uploaded) use ($size) {
            $percent = number_format(($uploaded / $size) * 100, 0);
            echo "uploading - {$percent}%\r";
        });
        echo "\n";
        $response = $this->client
            ->put($source->makeRequestPath("catalog/{$localPackage->getName()}/{$localPackage->getVersion()}/"))
            ->disableSsl()
            ->body([
                'package' => curl_file_create($localPath)
            ])
            ->headers($source->makeAuthHeaders())
            ->execute();
        $this->client->resetProgressFunction();
        $response
            ->awaitCode(200, function (Response $response) {
                echo "Successful upload package\n";
            })
            ->awaitCodes([400, 401, 403], $this->getErrorResponseCallback());

        unlink($localPath);
    }

    public function download(IRemotePackage $remotePackage): ILocalPackage
    {
        $this->client->setProgressFunction(function ($headers, $downloaded, $uploaded) use ($remotePackage) {
            $percent = 0;
            if (key_exists('Content-Length', $headers)) {
                $download_size = $headers['Content-Length'];
                $percent = number_format(($downloaded / $download_size) * 100, 0);
            }
            echo "Download package {$remotePackage->getName()}:{$remotePackage->getVersion()} - {$percent}%\r";
        });
        echo "\n";
        $response = $this->client
            ->get($remotePackage->getSource()->makeRequestPath("catalog/{$remotePackage->getName()}/{$remotePackage->getVersion()}/"))
            ->disableSsl()
            ->query([
                'package' => $remotePackage->getName(),
                'version' => $remotePackage->getVersion()
            ])
            ->headers($remotePackage->getSource()->makeAuthHeaders())
            ->execute();

        $localPackage = null;
        $response
            ->awaitCode(200, function (Response $response) use ($remotePackage, &$localPackage) {
                $localPackage = $this->localManager->save($remotePackage->getName(), $remotePackage->getVersion(), $response->text());
            })
            ->awaitCodes([400, 401, 403, 404], $this->getErrorResponseCallback());
        $this->client->resetProgressFunction();
        return $localPackage;
    }

    public function restore(IConfigurationCollection $configurationCollection): void
    {
        $packages = $configurationCollection->getDepends();
        $this->restorePackages($packages);
    }

    private function restorePackages(array $packages): void
    {
        foreach ($packages as $name => $version) {
            if (!is_null($localPackage = $this->localManager->get($name, $version))) {
                $this->restorePackages($localPackage->getDepends());
            } else {
                $package = $this->find($name, $version);
                if (is_null($package))
                    throw new PackageNotFoundException("Package {$name}:{$version} not found");

                $this->download($package);
                $this->restoreRemoteDepends($package);
            }
        }
    }

    private function restoreRemoteDepends(IRemotePackage $remotePackage): void
    {
        foreach ($remotePackage->getDepends() as $name => $version) {
            if (is_null($this->localManager->get($name, $version))) {
                $localPackage = $this->download(new RemotePackage($name, $version, $remotePackage->getSource()));
                $this->restorePackages($localPackage->getDepends());
            }
        }
    }

    private function getErrorResponseCallback(): Closure
    {
        return function (Response $response) {
            throw new BadRequestException($response->json()['error']);
        };
    }

    private function compactLocalPackage(ILocalPackage $package): string
    {
        return Compactor::compact($package, $this->tmpDirectory, microtime());
    }
}