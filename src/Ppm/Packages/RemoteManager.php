<?php

namespace Ppm\Packages;

use Closure;
use Ppm\Framework\Filesystem\Directory;
use Ppm\Framework\Filesystem\File;
use Ppm\Framework\Filesystem\TmpManager;
use Ppm\Framework\Network\Client\Body\FormUrlencodedBody;
use Ppm\Framework\Network\Client\Body\JsonBody;
use Ppm\Framework\Network\Client\HttpRequestHelper;
use Ppm\Framework\Network\Client\HttpResponse;
use Ppm\Packages\Exceptions\BadRequestException;
use Ppm\Packages\Sources\Source;
use Ppm\Packages\Sources\Sources;
use Ppm\Packages\Storage\DependencyTreeBuilderRemote;

class RemoteManager
{
    private Sources $sources;
    private TmpManager $tmp;
    private string $catalogDirectory;

    public function __construct(Sources $sources, TmpManager $tmp, string $catalogDirectory)
    {
        $this->sources = $sources;
        $this->tmp = $tmp;
        $this->catalogDirectory = $catalogDirectory;
    }

    public function updateCatalogs(): void
    {
        Directory::clearDirectory($this->catalogDirectory);
        foreach ($this->sources as $key => $source) {
            $response = HttpRequestHelper::get($source->makeRequestPath("catalog"))
                ->setHeaders($source->makeAuthHeaders())->send();

            $response
                ->awaitCode(200, function (HttpResponse $response) use ($source) {
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
        $response = HttpRequestHelper::put($source->makeRequestPath("catalog/upload"))
            ->setBody(new FormUrlencodedBody([
                'package' => curl_file_create($path)
            ]))
            ->setHeaders($source->makeAuthHeaders())
            ->sendBlocks(5, function ($total, $uploaded) {
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
        $response = HttpRequestHelper::post($source->makeRequestPath("catalog/download"))
            ->setBody(new JsonBody($packages))
            ->setHeaders($source->makeAuthHeaders())
            ->sendBlocks()
            ->waitResponse(function ($total, $downloaded) {
                $percent = number_format(($downloaded / $total) * 100, 0);
                echo "Downloading - {$percent}%\r";
            });
        echo "\n";
        $response
            ->awaitCode(200, function (HttpResponse $response) use (&$tmpFile) {
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
        return function (HttpResponse $response) {
            throw new BadRequestException($response->json()['error']);
        };
    }
}