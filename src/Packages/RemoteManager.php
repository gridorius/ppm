<?php

namespace Packages;

use Builder\Configuration\Contracts\IConfigurationCollection;
use Closure;
use Exception;
use Packages\Contracts\ILocalManager;
use Packages\Contracts\ILocalPackage;
use Packages\Contracts\IRemoteManager;
use Packages\Contracts\IRemotePackage;
use Packages\Contracts\ISource;
use Packages\Contracts\ISources;
use Packages\Http\Client;
use Packages\Http\Response;

class RemoteManager implements IRemoteManager
{
    private ISources $sources;
    private Client $client;

    private ILocalManager $localManager;

    /**
     * @param ISources $sources
     */
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
                ->get($source->makeRequestPath('/catalog/find'))
                ->query([
                    'package' => $name,
                    'version' => $version
                ])
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
            ->post($source->makeRequestPath('/catalog/upload'))
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

    public function download(IRemotePackage $remotePackage): void
    {
        $this->client->setProgressFunction(function ($percent) use ($remotePackage) {
            echo "Download package {$remotePackage->getName()}:{$remotePackage->getVersion()} - {$percent}%\r";
        });
        echo "\n";
        $response = $this->client
            ->get($remotePackage->getSource()->makeRequestPath('/download'))
            ->query([
                'package' => $remotePackage->getName(),
                'version' => $remotePackage->getVersion()
            ])
            ->headers($remotePackage->getSource()->makeAuthHeaders())
            ->execute();

        $response
            ->awaitCode(200, function (Response $response) use ($remotePackage) {
                $this->localManager->save($remotePackage->getName(), $remotePackage->getVersion(), $response['content']);
            })
            ->awaitCodes([400, 401, 403, 404], $this->getErrorResponseCallback());
        $this->client->resetProgressFunction();
    }

    public function restore(IConfigurationCollection $configurationCollection): void
    {
        $packages = $configurationCollection->getPackages();
        $this->restorePackages($packages);
    }

    private function restorePackages(array $packages)
    {
        foreach ($packages as $name => $version) {
            if ($this->localManager->exist($name, $version)) {
                $localPackage = $this->localManager->get($name, $version);
                $this->restorePackages($localPackage->getDepends());
            } else {
                $package = $this->find($name, $version);
                if (is_null($package))
                    throw new Exception("Package {$name}:{$version} not found");

                $this->download($package);
                $this->restoreRemoteDepends($package);
            }
        }
    }

    private function restoreRemoteDepends(IRemotePackage $remotePackage)
    {
        foreach ($remotePackage->getDepends() as $depend) {
            if (!$this->localManager->exist($depend->getName(), $depend->getVersion())) {
                $this->download($depend);
                $this->restoreRemoteDepends($depend);
            }
        }
    }

    private function getErrorResponseCallback(): Closure
    {
        return function (Response $response) {
            throw new Exception($response->json()['error']);
        };
    }
}