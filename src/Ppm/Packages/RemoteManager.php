<?php

namespace Ppm\Packages;

use Closure;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\File;
use Ppm\Framework\Filesystem\TmpManager;
use Ppm\Framework\Network\Client\Body\FormUrlencodedBody;
use Ppm\Framework\Network\Client\Body\JsonBody;
use Ppm\Framework\Network\Client\HttpClient;
use Ppm\Framework\Network\Client\Response;
use Ppm\Packages\Exceptions\BadRequestException;
use Ppm\Packages\Sources\Source;
use Ppm\Packages\Sources\Sources;
use Ppm\Packages\Storage\DependencyTreeBuilderRemote;

class RemoteManager
{
    private Sources $sources;
    private HttpClient $client;
    private TmpManager $tmp;

    private string $catalogDirectory;

    public function __construct(Sources $sources, TmpManager $tmp, string $catalogDirectory)
    {
        $this->sources = $sources;
        $this->client = new HttpClient();
        $this->tmp = $tmp;
        $this->catalogDirectory = $catalogDirectory;
    }

    public function updateCatalogs(): void
    {
        Directory::clearDirectory($this->catalogDirectory);
        foreach ($this->sources as $key => $source) {
            $request = $this->client
                ->get($source->makeRequestPath("catalog"))
                ->setHeaders($source->makeAuthHeaders());
            $response = $this->client->send($request);

            $response
                ->awaitCode(200, function (Response $response) use ($source) {
                    file_put_contents($this->getSourceCatalogPath($source), $response->text());
                })
                ->awaitCodes([400, 401, 403, 404], $this->getErrorResponseCallback());
        }
    }

    public function getSources(): Sources
    {
        return $this->sources;
    }

    /**
     * @return DependencyTreeBuilderRemote[]
     */
    public function getCatalog(): array
    {
        $result = [];
        foreach ($this->sources as $source)
            $result[$source->getId()] = new DependencyTreeBuilderRemote(json_decode(file_get_contents($this->getSourceCatalogPath($source)), true));

        return $result;
    }

    public function upload(string $path, Source $source): void
    {
        echo "Package compacted start uploading" . PHP_EOL;
        $request = $this->client
            ->put($source->makeRequestPath("catalog/upload"))
            ->setBody(new FormUrlencodedBody([
                'package' => curl_file_create($path)
            ]))
            ->setHeaders($source->makeAuthHeaders());
        $response = $this
            ->client
            ->prepareClient($request)
            ->sendBlocks(function ($total, $uploaded) {
                $percent = number_format(($uploaded / $total) * 100, 0);
                echo "uploading - {$percent}%\r";
            })->waitResponse();
        echo "\n";
        $response
            ->awaitCode(200, function () {
                echo "Successful upload package\n";
            })
            ->awaitCodes([400, 401, 403], $this->getErrorResponseCallback());
    }

    public function downloadFrom(string $sourceId, array $packages): File
    {
        $source = $this->sources[$sourceId];
        $tmpFile = null;
        $request = $this->client
            ->post($source->makeRequestPath("catalog/download"))
            ->setBody(new JsonBody($packages))
            ->setHeaders($source->makeAuthHeaders());

        $response = $this->client
            ->prepareClient($request)
            ->sendBlocks()
            ->waitResponse(function ($total, $downloaded) {
                $percent = number_format(($downloaded / $total) * 100, 0);
                echo "Downloading - {$percent}%\r";
            });
        echo "\n";
        $response
            ->awaitCode(200, function (Response $response) use (&$tmpFile) {
                $tmpFile = $this->tmp->createFile('tar', $response->text());
            })
            ->awaitCodes([400, 401, 403, 404], $this->getErrorResponseCallback());
        return $tmpFile;
    }

    private function getSourceCatalogPath(Source $source): string
    {
        return $this->catalogDirectory . DIRECTORY_SEPARATOR . $source->getId() . '.json';
    }

    private function getErrorResponseCallback(): Closure
    {
        return function (Response $response) {
            throw new BadRequestException($response->json()['error']);
        };
    }
}