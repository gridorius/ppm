<?php

namespace Ppm\Framework\Network\Client;


class RawRequestBuilder
{
    public static function build(RequestData $request): string
    {
        $lines = [];
        $lines[] = sprintf("%s %s HTTP/1.1", $request->getMethod(), $request->getUrl());
        foreach ($request->getHeaders() as $name => $value) {
            $lines[] = "{$name}: {$value}";
        }

        if (!$request->setCookies()->isEmpty())
            $lines[] = $request->setCookies()->buildHeader();

        $body = $request->getBody();
        if (!is_null($body)) {
            $content = $body->buildRequestBody();
            $lines[] = 'Content-Type: ' . $body->getContentType();
            $lines[] = 'Content-Length: ' . strlen($content);
            $lines[] = '';
            $lines[] = $content;
        }

        return implode("\r\n", $lines);
    }
}