<?php

namespace Packages;

use Builder\Configuration\Contracts\IConfigurationCollection;
use Closure;
use Packages\Contracts\IRemoteManager;
use Packages\Contracts\IRemotePackage;
use Packages\Contracts\ISource;
use Packages\Contracts\ISources;
use Packages\Exceptions\BadRequestException;
use Packages\Exceptions\PackageNotFoundException;
use Packages\Http\Client;
use Packages\Http\Response;
use PpmRegistry\Contracts\ILocalManager;
use PpmRegistry\Contracts\ILocalPackage;

class RemoteManager implements IRemoteManager
{
    private ISources $sources;
    private Client $client;

    private ILocalManager $localManager;

    public function __construct(ISources $sources, ILocalManager $localManager)
    {
        $this->sources = $sources;
        $this->localManager = $localManager;
        $this->client = new Client();
    }

    public function find(string $name, string $version): ?IRemotePackage
    {
        $foundPackage = null;
        foreach ($this->sources as $source) {
            $response = $this
                ->client
                ->get($source->makeRequestPath("/catalog/{$name}/{$version}/info/"))
                ->headers($source->makeAuthHeaders())
                ->execute();

            $response
                ->awaitCode(200, function (Response $response) use (&$foundPackage, $source) {
                    $data = $response->json();
                    $foundPackage = new RemotePackage($data['name'], $data['verion'], $source, $data['depends']);
                })
                ->awaitCodes([400, 401, 403, 404], $this->getErrorResponseCallback());
        }

        return $foundPackage;
    }

    public function upload(ILocalPackage $localPackage, ISource $source): void
    {
        $response = $this->client
            ->put($source->makeRequestPath("/catalog/{$localPackage->getName()}/{$localPackage->getVersion()}/"))
            ->body([
                'package' => curl_file_create($localPackage->getPath())
            ])
            ->headers($source->makeAuthHeaders())
            ->execute();

        $response
            ->awaitCode(200, function (Response $response) {
                echo "Successful upload package\n";
            })
            ->awaitCodes([400, 401, 403], $this->getErrorResponseCallback());
    }

    public function download(IRemotePackage $remotePackage): ILocalPackage
    {
        $this->client->setProgressFunction(function ($percent) use ($remotePackage) {
            echo "Download package {$remotePackage->getName()}:{$remotePackage->getVersion()} - {$percent}%\r";
        });
        echo "\n";
        $response = $this->client
            ->get($remotePackage->getSource()->makeRequestPath("/catalog/{$remotePackage->getName()}/{$remotePackage->getVersion()}/"))
            ->query([
                'package' => $remotePackage->getName(),
                'version' => $remotePackage->getVersion()
            ])
            ->headers($remotePackage->getSource()->makeAuthHeaders())
            ->execute();

        $localPackage = null;
        $response
            ->awaitCode(200, function (Response $response) use ($remotePackage, &$localPackage) {
                $localPackage = $this->localManager->save($remotePackage->getName(), $remotePackage->getVersion(), $response['content']);
            })
            ->awaitCodes([400, 401, 403, 404], $this->getErrorResponseCallback());
        $this->client->resetProgressFunction();
        return $localPackage;
    }

    public function restore(IConfigurationCollection $configurationCollection): void
    {
        $packages = $configurationCollection->getPackages();
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
}