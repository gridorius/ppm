<?php

namespace Packages\Http;

use Closure;

class Client
{
    protected ?Closure $progressFunction;

    public function setProgressFunction(Closure $function)
    {
        $this->progressFunction = $function;
    }

    public function resetProgressFunction()
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

    public function executeQuery(string $url, string $method, array $headers = [], array $query = [], $data = null): Response
    {
        if (!empty($query))
            $url .= '?' . http_build_query($query);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if (!empty($this->progressFunction)) {
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $download_size, $downloaded) {
                $percent = ($downloaded / $download_size) * 100;
                call_user_func_array($this->progressFunction, [$percent, $downloaded, $download_size]);
            });
        }
        if (!empty($headers)) {
            $preparedHeaders = [];
            foreach ($headers as $header => $content) {
                $preparedHeaders[] = $header . ': ' . $content;
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $preparedHeaders);
        }

        $responseHeaders = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header_line) use (&$responseHeaders) {
            $header = explode(':', $header_line, 2);
            if (count($header) == 2) {
                $responseHeaders[$header[0]] = $header[1];
            } else {
                [$protocol, $code, $status] = explode(' ', $header, 3);
                $responseHeaders['protocol'] = $protocol;
                $responseHeaders['code'] = $code;
                $responseHeaders['status'] = $status;
            }
            return strlen($header_line);
        });

        $result = curl_exec($ch);
        curl_close($ch);

        return new Response($responseHeaders, $result);
    }
}