<?php

namespace Packages\Http;

use Assembly\Exception;
use Closure;

class Client
{
    protected ?Closure $progressFunction;

    public function setProgressFunction(Closure $function): void
    {
        $this->progressFunction = $function;
    }

    public function resetProgressFunction(): void
    {
        $this->progressFunction = null;
    }

    public function get(string $url): QueryBuilder
    {
        return new QueryBuilder($url, 'GET', $this);
    }

    public function post(string $url): QueryBuilder
    {
        return new QueryBuilder($url, 'POST', $this);
    }

    public function put(string $url): QueryBuilder
    {
        return new QueryBuilder($url, 'PUT', $this);
    }

    public function executeQuery(string $url, string $method, array $headers = [], array $query = [], $data = null, $useSsl = true): Response
    {
        if (!empty($query))
            $url .= '?' . http_build_query($query);
        $responseHeaders = [];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $useSsl);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($data))
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if (!empty($this->progressFunction)) {
            curl_setopt($ch,
                CURLOPT_PROGRESSFUNCTION,
                function ($resource, $download_size = 0, $downloaded = 0, $upload_size = 0, $uploaded = 0) use (&$responseHeaders) {
                    call_user_func($this->progressFunction, $responseHeaders, $downloaded, $uploaded);
                });
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        }
        if (!empty($headers)) {
            $preparedHeaders = [];
            foreach ($headers as $header => $content) {
                $preparedHeaders[] = $header . ': ' . $content;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $preparedHeaders);
        }

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header_line) use (&$responseHeaders) {
            $header = explode(':', $header_line, 2);
            if (count($header) == 2) {
                $responseHeaders[$header[0]] = trim($header[1]);
            } else if (!empty(trim($header_line))) {
                $first = explode(' ', trim($header_line), 3);
                $responseHeaders['protocol'] = $first[0];
                $responseHeaders['code'] = $first[1];
                $responseHeaders['status'] = $first[2] ?? '';
            }
            return strlen($header_line);
        });

        $result = curl_exec($ch);
        if ($error = curl_error($ch))
            throw new Exception($error);
        curl_close($ch);

        return new Response($responseHeaders, $result);
    }
}