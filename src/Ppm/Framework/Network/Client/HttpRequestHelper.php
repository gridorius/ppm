<?php

namespace Ppm\Framework\Network\Client;

use Ppm\Framework\Network\Constants\RequestMethods;

class HttpRequestHelper
{
    public static function get(string $url): HttpRequestAction
    {
        return new HttpRequestAction($url);
    }

    public static function post(string $url): HttpRequestAction
    {
        return new HttpRequestAction($url, RequestMethods::POST);
    }

    public static function put(string $url): HttpRequestAction
    {
        return new HttpRequestAction($url, RequestMethods::PUT);
    }

    public static function patch(string $url): HttpRequestAction
    {
        return new HttpRequestAction($url, RequestMethods::PATCH);
    }

    public static function delete(string $url): HttpRequestAction
    {
        return new HttpRequestAction($url, RequestMethods::DELETE);
    }

    public static function head(string $url): HttpRequestAction
    {
        return new HttpRequestAction($url, RequestMethods::HEAD);
    }

    public static function options(string $url): HttpRequestAction
    {
        return new HttpRequestAction($url, RequestMethods::OPTIONS);
    }

    public static function sendParallel(array $requests, int $timeout = 5): HttpAsyncReader
    {
        return new HttpAsyncReader($requests, $timeout);
    }
}