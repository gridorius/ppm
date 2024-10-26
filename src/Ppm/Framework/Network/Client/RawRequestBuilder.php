<?php

namespace Ppm\Framework\Network\Client;


class RawRequestBuilder
{
    public static function build(HttpRequest $request): string
    {
        $headers = [];
        $urlData = $request->getUrlData();
        $headers[] = sprintf("%s %s HTTP/1.1", $request->getMethod(), $urlData['path'] ?? '/');
        $headers[] = "Host: " . $urlData['host'];
        foreach ($request->getHeaders() as $header)
            $headers[] = $header;

        if (!$request->setCookies()->isEmpty())
            $headers[] = $request->setCookies()->buildHeader();


        $body = $request->getBody();
        if (!is_null($body)) {
            $content = $body->buildRequestBody();
            $headers[] = 'Content-Type: ' . $body->getContentType();
            $headers[] = 'Content-Length: ' . strlen($content);
            $body = $content;
        }

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }
}